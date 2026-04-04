<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Trait;

use BackedEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Oltrematica\RoleLite\Models\Permission;
use Oltrematica\RoleLite\Models\Role;
use Oltrematica\RoleLite\Services\PermissionService;

/**
 * Provides permission checking methods to models that use HasRoles.
 *
 * @mixin Model
 * @mixin HasRoles
 */
trait HasPermissions
{
    public function hasPermissionTo(string|BackedEnum $permission): bool
    {
        $permissionName = $this->normalizePermission($permission);

        return app(PermissionService::class)->userHasPermission($this, $permissionName);
    }

    public function hasAnyPermission(string|BackedEnum ...$permissions): bool
    {
        if ($permissions === []) {
            return false;
        }

        $service = app(PermissionService::class);

        foreach ($permissions as $permission) {
            $permissionName = $this->normalizePermission($permission);
            if ($service->userHasPermission($this, $permissionName)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions(string|BackedEnum ...$permissions): bool
    {
        if ($permissions === []) {
            return true;
        }

        $service = app(PermissionService::class);

        foreach ($permissions as $permission) {
            $permissionName = $this->normalizePermission($permission);
            if (! $service->userHasPermission($this, $permissionName)) {
                return false;
            }
        }

        return true;
    }

    public function canDo(string $modelClass, string $action): bool
    {
        return app(PermissionService::class)->userCan($this, $modelClass, $action);
    }

    /**
     * Grant a permission to the model via a specific role, or the first role if none specified.
     * Creates the permission if it doesn't exist.
     */
    public function givePermissionTo(string|BackedEnum $permission, ?Role $role = null): self
    {
        $permissionName = $this->normalizePermission($permission);
        $permissionModel = Permission::firstOrCreate(['name' => $permissionName]);

        $role ??= $this->roles()->first();
        if ($role) {
            $role->grantPermission($permissionModel);
        }

        return $this;
    }

    /**
     * Revoke a permission from the model via a specific role, or the first role if none specified.
     */
    public function revokePermissionTo(string|BackedEnum $permission, ?Role $role = null): self
    {
        $permissionName = $this->normalizePermission($permission);
        $permissionModel = Permission::query()->where('name', $permissionName)->first();

        if ($permissionModel) {
            $role ??= $this->roles()->first();
            if ($role) {
                $role->revokePermission($permissionModel);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Permission>
     */
    public function getAllPermissions(): Collection
    {
        return Permission::query()
            ->whereHas('roles', function ($query): void {
                $query->whereIn('roles.id', $this->roles()->pluck('roles.id'));
            })
            ->get();
    }

    private function normalizePermission(string|BackedEnum $permission): string
    {
        if ($permission instanceof BackedEnum) {
            return (string) $permission->value;
        }

        return $permission;
    }
}
