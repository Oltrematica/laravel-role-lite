<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Trait;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Collection;
use Oltrematica\RoleLite\Models\Role;
use Oltrematica\RoleLite\Models\RoleUser;

/**
 * @mixin Model
 * @mixin User
 */
trait HasRoles
{
    /**
     * Define a many-to-many relationship with Role.
     *
     * @return BelongsToMany<Role, covariant $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->using(RoleUser::class);
    }

    /**
     * Check if the model has all of the given roles.
     *
     * @param  string|BackedEnum  ...$roles
     */
    public function hasRoles(...$roles): bool
    {
        if ($roles === []) {
            return true; // Consistent with hasAnyRoles returning false for no roles
        }
        $roleNames = $this->normalizeRoles(...$roles);

        return $this->roles->pluck('name')->intersect($roleNames)->count() === count($roleNames);
    }

    /**
     * Check if the model has any of the given roles.
     *
     * @param  string|BackedEnum  ...$roles
     */
    public function hasAnyRoles(...$roles): bool
    {
        if ($roles === []) {
            return false;
        }
        $roleNames = $this->normalizeRoles(...$roles);

        return $this->roles->pluck('name')->intersect($roleNames)->isNotEmpty();
    }

    /**
     * Assign the given role to the model.
     */
    public function assignRole(string|BackedEnum $role): Model
    {
        $roleName = $this->normalizeRole($role);
        $roleModel = Role::query()->firstOrCreate(['name' => $roleName]);
        // Check if the user already has the role to avoid redundant database operations and event dispatches
        if (! $this->roles()->where('role_id', $roleModel->id)->exists()) {
            $this->roles()->attach($roleModel->id); // Use attach for clarity on adding a single role
            $this->unsetRelation('roles');
        }

        return $this;
    }

    /**
     * Remove the given role from the model.
     */
    public function removeRole(string|BackedEnum $role): Model
    {
        $roleName = $this->normalizeRole($role);
        $roleModel = Role::query()->where('name', $roleName)->first();
        if ($roleModel && $this->roles()->where('role_id', $roleModel->id)->exists()) {
            $this->roles()->detach($roleModel->id); // Pass ID to detach for consistency
            $this->unsetRelation('roles');
        }

        return $this;
    }

    /**
     * Sync the given roles to the model.
     *
     * @param  string|BackedEnum  ...$roles
     */
    public function syncRoles(...$roles): Model
    {
        $roleNames = $this->normalizeRoles(...$roles);
        $roleIds = collect($roleNames)->map(fn (string $roleName) => Role::query()->firstOrCreate(['name' => $roleName])->id)->all();

        $this->roles()->sync($roleIds);
        $this->unsetRelation('roles');

        return $this;
    }

    /**
     * Check if the model has the given role.
     */
    public function hasRole(string|BackedEnum $role): bool
    {
        $roleName = $this->normalizeRole($role);

        return $this->roles->contains('name', $roleName);
    }

    /**
     * Check if the user has some role.
     *
     * @return bool if the user has some role
     */
    public function hasSomeRoles(): bool
    {
        return $this->roles->isNotEmpty();
    }

    /**
     * Check if the user has no roles.
     *
     * @return bool if the user has not any roles
     */
    public function hasNoRoles(): bool
    {
        return $this->roles->isEmpty();
    }

    /**
     * Normalize a single role to its string representation.
     */
    private function normalizeRole(string|BackedEnum $role): string
    {
        if (($role instanceof BackedEnum) && is_string($value = $role->value)) {
            return $value;
        }

        /** @var string $role */
        return $role;
    }

    /**
     * Normalize a list of roles to their string representation.
     *
     * @param  string|BackedEnum  ...$roles
     * @return Collection<int, string>
     */
    private function normalizeRoles(...$roles): Collection
    {
        return collect($roles)->flatten()->map(fn (BackedEnum|string $role) => $this->normalizeRole($role))->unique();
    }
}
