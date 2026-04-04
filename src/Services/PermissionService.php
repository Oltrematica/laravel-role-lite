<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Oltrematica\RoleLite\Models\Permission;
use Oltrematica\RoleLite\Models\Role;

class PermissionService
{
    /**
     * In-memory cache to avoid repeated cache driver queries within the same request.
     *
     * @var array<int, array<string, bool>>|null
     */
    private ?array $inMemoryPermissions = null;

    /**
     * Check if a role has a specific permission.
     */
    public function roleHasPermission(int $roleId, string $permissionName): bool
    {
        $permissions = $this->getCachedPermissions();

        return isset($permissions[$roleId][$permissionName]);
    }

    /**
     * Check if a user (or any model using HasRoles) has a specific permission through any of their roles.
     */
    public function userHasPermission(Model $user, string $permissionName): bool
    {
        $permissions = $this->getCachedPermissions();

        /** @var \Illuminate\Database\Eloquent\Collection<int, Role> $roles */
        $roles = $user->roles;

        foreach ($roles as $role) {
            if (isset($permissions[$role->id][$permissionName])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user has permission for a specific model class and action.
     */
    public function userCan(Model $user, string $modelClass, string $action): bool
    {
        $permissionName = Permission::buildPermissionName($modelClass, $action);

        return $this->userHasPermission($user, $permissionName);
    }

    /**
     * Toggle a permission for a role. Returns true if granted, false if revoked.
     */
    public function toggleRolePermission(Role $role, Permission $permission): bool
    {
        $hasPermission = $role->permissions()->where('permission_id', $permission->id)->exists();

        if ($hasPermission) {
            $role->permissions()->detach($permission->id);
        } else {
            $role->permissions()->attach($permission->id);
        }

        $this->clearCache();

        return ! $hasPermission;
    }

    /**
     * Clear all permission caches (both distributed and in-memory).
     */
    public function clearCache(): void
    {
        Cache::forget($this->cacheKey());
        $this->inMemoryPermissions = null;
    }

    /**
     * Get all permissions for all roles, with two-tier caching.
     *
     * @return array<int, array<string, bool>>
     */
    private function getCachedPermissions(): array
    {
        if ($this->inMemoryPermissions !== null) {
            return $this->inMemoryPermissions;
        }

        $ttl = ConfigService::getCacheTtl();

        if ($ttl === 0) {
            $this->inMemoryPermissions = $this->loadPermissionsFromDatabase();

            return $this->inMemoryPermissions;
        }

        $this->inMemoryPermissions = Cache::remember($this->cacheKey(), $ttl, fn (): array => $this->loadPermissionsFromDatabase());

        return $this->inMemoryPermissions;
    }

    /**
     * @return array<int, array<string, bool>>
     */
    private function loadPermissionsFromDatabase(): array
    {
        $permissions = [];

        $roles = Role::with('permissions')->get();
        foreach ($roles as $role) {
            $permissions[$role->id] = [];
            foreach ($role->permissions as $permission) {
                $permissions[$role->id][$permission->name] = true;
            }
        }

        return $permissions;
    }

    private function cacheKey(): string
    {
        return ConfigService::getCachePrefix().'_role_permissions';
    }
}
