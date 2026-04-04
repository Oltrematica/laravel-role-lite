<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Trait;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Oltrematica\RoleLite\Models\Permission;
use Oltrematica\RoleLite\Services\PermissionService;

/**
 * Trait for Laravel Policies to centralize database-driven permission checking.
 *
 * Usage in a policy:
 *
 *     class CustomerPolicy
 *     {
 *         use ChecksPermissions;
 *
 *         public function viewAny(User $user): bool
 *         {
 *             return $this->checkPermission($user, 'view_any');
 *         }
 *     }
 *
 * The model class is auto-derived from the policy name:
 * CustomerPolicy → 'customer', ServiceVisitPolicy → 'service_visit'
 */
trait ChecksPermissions
{
    protected function checkPermission(Model $user, string $action, ?string $modelClass = null): bool
    {
        $modelClass ??= $this->getModelClass();
        $permissionName = Permission::buildPermissionName($modelClass, $action);

        return app(PermissionService::class)->userHasPermission($user, $permissionName);
    }

    protected function getModelClass(): string
    {
        return Str::replace('Policy', '', class_basename(static::class));
    }

    protected function getPermissionName(string $action, ?string $modelClass = null): string
    {
        $modelClass ??= $this->getModelClass();

        return Permission::buildPermissionName($modelClass, $action);
    }
}
