<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Oltrematica\RoleLite\Services\ConfigService;

/**
 * @property int $id
 * @property string $name
 */
class Role extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(ConfigService::getRolesTable());
    }

    /**
     * @return BelongsToMany<Model, $this>
     */
    public function users(): BelongsToMany
    {
        /** @var class-string<Model> $relatedModel */
        $relatedModel = ConfigService::getUserModel();

        return $this->belongsToMany(
            related: $relatedModel,
            table: ConfigService::getRoleUserTable(),
            foreignPivotKey: 'role_id',
            relatedPivotKey: 'user_id')->using(RoleUser::class);
    }

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Permission::class,
            table: ConfigService::getRolePermissionTable(),
            foreignPivotKey: 'role_id',
            relatedPivotKey: 'permission_id',
        )->using(PermissionRole::class);
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    public function grantPermission(Permission $permission): void
    {
        if (! $this->permissions()->where('permission_id', $permission->id)->exists()) {
            $this->permissions()->attach($permission->id);
            $this->unsetRelation('permissions');
        }
    }

    public function revokePermission(Permission $permission): void
    {
        $this->permissions()->detach($permission->id);
        $this->unsetRelation('permissions');
    }

    /**
     * @param  array<int>  $permissionIds
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
        $this->unsetRelation('permissions');
    }
}
