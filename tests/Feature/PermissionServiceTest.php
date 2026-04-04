<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Oltrematica\RoleLite\Models\Permission;
use Oltrematica\RoleLite\Models\Role;
use Oltrematica\RoleLite\Services\PermissionService;
use Oltrematica\RoleLite\Tests\TestModels\User;

beforeEach(function (): void {
    $this->service = app(PermissionService::class);
});

describe('roleHasPermission', function (): void {
    test('returns true when role has the permission', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->grantPermission($permission);

        expect($this->service->roleHasPermission($role->id, 'user.view'))->toBeTrue();
    });

    test('returns false when role does not have the permission', function (): void {
        $role = Role::query()->create(['name' => 'admin']);

        expect($this->service->roleHasPermission($role->id, 'user.view'))->toBeFalse();
    });
});

describe('userHasPermission', function (): void {
    test('returns true when user has permission through role', function (): void {
        $user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->grantPermission($permission);
        $user->assignRole('admin');

        expect($this->service->userHasPermission($user, 'user.view'))->toBeTrue();
    });

    test('returns false when user does not have permission', function (): void {
        $user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
        $user->assignRole('viewer');

        expect($this->service->userHasPermission($user, 'user.delete'))->toBeFalse();
    });

    test('returns true when user has permission through any of multiple roles', function (): void {
        $user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
        $roleA = Role::query()->create(['name' => 'role-a']);
        $roleB = Role::query()->create(['name' => 'role-b']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $roleB->grantPermission($permission);
        $user->assignRole('role-a');
        $user->assignRole('role-b');

        expect($this->service->userHasPermission($user, 'user.view'))->toBeTrue();
    });
});

describe('userCan', function (): void {
    test('returns true for model+action permission', function (): void {
        $user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'customer.view_any']);
        $role->grantPermission($permission);
        $user->assignRole('admin');

        expect($this->service->userCan($user, 'App\Models\Customer', 'view_any'))->toBeTrue();
    });

    test('returns false when user cannot perform action on model', function (): void {
        $user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
        $user->assignRole('viewer');

        expect($this->service->userCan($user, 'App\Models\Customer', 'delete'))->toBeFalse();
    });
});

describe('caching', function (): void {
    test('caches permissions and serves from cache', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->grantPermission($permission);

        $this->service->roleHasPermission($role->id, 'user.view');

        expect(Cache::has('role_lite_role_permissions'))->toBeTrue();
    });

    test('clearCache removes cached permissions', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->grantPermission($permission);

        $this->service->roleHasPermission($role->id, 'user.view');
        $this->service->clearCache();

        expect(Cache::has('role_lite_role_permissions'))->toBeFalse();
    });

    test('serves fresh data after clearCache', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $p1 = Permission::query()->create(['name' => 'user.view']);
        $role->grantPermission($p1);

        expect($this->service->roleHasPermission($role->id, 'user.view'))->toBeTrue();

        $p2 = Permission::query()->create(['name' => 'user.create']);
        $role->grantPermission($p2);
        $this->service->clearCache();

        expect($this->service->roleHasPermission($role->id, 'user.create'))->toBeTrue();
    });
});

describe('toggleRolePermission', function (): void {
    test('grants permission when not present and returns true', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);

        $result = $this->service->toggleRolePermission($role, $permission);

        expect($result)->toBeTrue()
            ->and($role->hasPermission('user.view'))->toBeTrue();
    });

    test('revokes permission when present and returns false', function (): void {
        $role = Role::query()->create(['name' => 'admin']);
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role->grantPermission($permission);
        $this->service->clearCache();

        $result = $this->service->toggleRolePermission($role, $permission);

        expect($result)->toBeFalse()
            ->and($role->hasPermission('user.view'))->toBeFalse();
    });
});
