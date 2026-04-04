<?php

declare(strict_types=1);

use Oltrematica\RoleLite\Models\Permission;
use Oltrematica\RoleLite\Models\Role;

describe('Role permissions relationship', function (): void {
    test('role has permissions relationship', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->permissions()->attach($permission->id);
        expect($role->permissions)->toHaveCount(1)
            ->and($role->permissions->first()->name)->toBe('user.view');
    });

    test('role can have multiple permissions', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $p1 = Permission::query()->create(['name' => 'user.view']);
        $p2 = Permission::query()->create(['name' => 'user.create']);
        $role->permissions()->attach([$p1->id, $p2->id]);
        expect($role->permissions)->toHaveCount(2);
    });
});

describe('Role hasPermission method', function (): void {
    test('returns true when role has the permission', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->permissions()->attach($permission->id);
        expect($role->hasPermission('user.view'))->toBeTrue();
    });

    test('returns false when role does not have the permission', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        expect($role->hasPermission('user.view'))->toBeFalse();
    });
});

describe('Role grantPermission method', function (): void {
    test('grants a permission to the role', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->grantPermission($permission);
        expect($role->hasPermission('user.view'))->toBeTrue();
    });

    test('granting same permission twice does not duplicate', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->grantPermission($permission);
        $role->grantPermission($permission);
        expect($role->permissions()->count())->toBe(1);
    });
});

describe('Role revokePermission method', function (): void {
    test('revokes a permission from the role', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->grantPermission($permission);
        $role->revokePermission($permission);
        expect($role->hasPermission('user.view'))->toBeFalse();
    });

    test('revoking a non-existing permission does not error', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->revokePermission($permission);
        expect($role->permissions()->count())->toBe(0);
    });
});

describe('Role syncPermissions method', function (): void {
    test('syncs permissions to the role', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $p1 = Permission::query()->create(['name' => 'user.view']);
        $p2 = Permission::query()->create(['name' => 'user.create']);
        $p3 = Permission::query()->create(['name' => 'user.delete']);
        $role->grantPermission($p1);
        $role->syncPermissions([$p2->id, $p3->id]);
        $role->unsetRelation('permissions');
        expect($role->hasPermission('user.view'))->toBeFalse()
            ->and($role->hasPermission('user.create'))->toBeTrue()
            ->and($role->hasPermission('user.delete'))->toBeTrue();
    });

    test('syncing with empty array removes all permissions', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->grantPermission($permission);
        $role->syncPermissions([]);
        $role->unsetRelation('permissions');
        expect($role->permissions)->toHaveCount(0);
    });
});
