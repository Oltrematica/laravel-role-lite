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
     * @return BelongsToMany<Role, covariant $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->using(RoleUser::class);
    }

    /**
     * @param  string|BackedEnum  ...$roles
     */
    public function hasRoles(...$roles): bool
    {
        $roleNames = $this->normalizeRoles(...$roles);

        return $this->roles->pluck('name')->intersect($roleNames)->count() === count($roleNames);
    }

    /**
     * @param  string|BackedEnum  ...$roles
     */
    public function hasAnyRoles(...$roles): bool
    {
        $roleNames = $this->normalizeRoles(...$roles);

        return $this->roles->pluck('name')->intersect($roleNames)->isNotEmpty();
    }

    public function assignRole(string|BackedEnum $role): Model
    {
        $roleName = $this->normalizeRole($role);
        $roleModel = Role::query()->firstOrCreate(['name' => $roleName]);
        $this->roles()->syncWithoutDetaching([$roleModel->id]);

        return $this;
    }

    public function removeRole(string|BackedEnum $role): Model
    {
        $roleName = $this->normalizeRole($role);
        $roleModel = Role::query()->where('name', $roleName)->first();
        if ($roleModel) {
            $this->roles()->detach($roleModel);
        }

        return $this;
    }

    /**
     * @param  string|BackedEnum  ...$roles
     */
    public function syncRoles(...$roles): Model
    {
        $roleNames = $this->normalizeRoles(...$roles);
        $roleIds = Role::query()->whereIn('name', $roleNames)->pluck('id');
        $this->roles()->sync($roleIds);

        return $this;
    }

    public function hasRole(string|BackedEnum $role): bool
    {
        $roleName = $this->normalizeRole($role);

        return $this->roles->contains('name', $roleName);
    }

    private function normalizeRole(string|BackedEnum $role): string
    {
        if (($role instanceof BackedEnum) && is_string($value = $role->value)) {
            return $value;
        }

        /** @var string $role */
        return $role;
    }

    /**
     * @param  string|BackedEnum  ...$roles
     * @return Collection<int, string>
     */
    private function normalizeRoles(...$roles): Collection
    {
        return collect($roles)->flatten()->map(fn (BackedEnum|string $role) => $this->normalizeRole($role))->unique();
    }
}
