<?php

declare(strict_types=1);

use Oltrematica\RoleLite\Models\Permission;
use Oltrematica\RoleLite\Models\Role;
use Oltrematica\RoleLite\Services\PermissionService;
use Oltrematica\RoleLite\Tests\TestModels\User;
use Oltrematica\RoleLite\Tests\TestModels\TestPermissionEnum;

beforeEach(function (): void {
    $this->user = User::query()->create(['name' => 'Test User', 'email' => 'test@test.com']);
    $this->role = Role::query()->create(['name' => 'editor']);
    $this->user->assignRole('editor');
    app(PermissionService::class)->clearCache();
});

describe('hasPermissionTo', function (): void {
    test('returns true when user has permission through role', function (): void {
        $permission = Permission::query()->create(['name' => 'post.update']);
        $this->role->grantPermission($permission);
        app(PermissionService::class)->clearCache();

        expect($this->user->hasPermissionTo('post.update'))->toBeTrue();
    });

    test('returns false when user does not have permission', function (): void {
        expect($this->user->hasPermissionTo('post.delete'))->toBeFalse();
    });

    test('accepts BackedEnum as permission name', function (): void {
        $permission = Permission::query()->create(['name' => 'user.view']);
        $this->role->grantPermission($permission);
        app(PermissionService::class)->clearCache();

        expect($this->user->hasPermissionTo(TestPermissionEnum::VIEW_USERS))->toBeTrue();
    });
});

describe('hasAnyPermission', function (): void {
    test('returns true when user has at least one permission', function (): void {
        $permission = Permission::query()->create(['name' => 'post.update']);
        $this->role->grantPermission($permission);
        app(PermissionService::class)->clearCache();

        expect($this->user->hasAnyPermission('post.update', 'post.delete'))->toBeTrue();
    });

    test('returns false when user has none of the permissions', function (): void {
        expect($this->user->hasAnyPermission('post.update', 'post.delete'))->toBeFalse();
    });

    test('returns false when no permissions passed', function (): void {
        expect($this->user->hasAnyPermission())->toBeFalse();
    });
});

describe('hasAllPermissions', function (): void {
    test('returns true when user has all permissions', function (): void {
        $p1 = Permission::query()->create(['name' => 'post.update']);
        $p2 = Permission::query()->create(['name' => 'post.view']);
        $this->role->grantPermission($p1);
        $this->role->grantPermission($p2);
        app(PermissionService::class)->clearCache();

        expect($this->user->hasAllPermissions('post.update', 'post.view'))->toBeTrue();
    });

    test('returns false when user is missing one permission', function (): void {
        $permission = Permission::query()->create(['name' => 'post.update']);
        $this->role->grantPermission($permission);
        app(PermissionService::class)->clearCache();

        expect($this->user->hasAllPermissions('post.update', 'post.delete'))->toBeFalse();
    });

    test('returns true when no permissions passed', function (): void {
        expect($this->user->hasAllPermissions())->toBeTrue();
    });
});

describe('canDo (model.action shorthand)', function (): void {
    test('returns true when user can perform action on model', function (): void {
        $permission = Permission::query()->create(['name' => 'customer.view_any']);
        $this->role->grantPermission($permission);
        app(PermissionService::class)->clearCache();

        expect($this->user->canDo('App\Models\Customer', 'view_any'))->toBeTrue();
    });

    test('returns false when user cannot perform action on model', function (): void {
        expect($this->user->canDo('App\Models\Customer', 'delete'))->toBeFalse();
    });
});

describe('givePermissionTo (via role)', function (): void {
    test('grants permission string to user first role', function (): void {
        $this->user->givePermissionTo('post.create');

        app(PermissionService::class)->clearCache();
        expect($this->user->hasPermissionTo('post.create'))->toBeTrue();
    });

    test('creates permission if it does not exist', function (): void {
        $this->user->givePermissionTo('brand.new.permission');

        expect(Permission::query()->where('name', 'brand.new.permission')->exists())->toBeTrue();
    });

    test('accepts BackedEnum', function (): void {
        $this->user->givePermissionTo(TestPermissionEnum::EDIT_POSTS);

        app(PermissionService::class)->clearCache();
        expect($this->user->hasPermissionTo('post.update'))->toBeTrue();
    });
});

describe('revokePermissionTo (via role)', function (): void {
    test('revokes permission from user first role', function (): void {
        $this->user->givePermissionTo('post.create');
        app(PermissionService::class)->clearCache();
        expect($this->user->hasPermissionTo('post.create'))->toBeTrue();

        $this->user->revokePermissionTo('post.create');
        app(PermissionService::class)->clearCache();
        expect($this->user->hasPermissionTo('post.create'))->toBeFalse();
    });
});

describe('getAllPermissions', function (): void {
    test('returns all permissions from all user roles', function (): void {
        $role2 = Role::query()->create(['name' => 'admin']);
        $this->user->assignRole('admin');

        $p1 = Permission::query()->create(['name' => 'post.view']);
        $p2 = Permission::query()->create(['name' => 'user.delete']);
        $this->role->grantPermission($p1);
        $role2->grantPermission($p2);

        $permissions = $this->user->getAllPermissions();

        expect($permissions->pluck('name')->toArray())->toEqualCanonicalizing(['post.view', 'user.delete']);
    });

    test('returns empty collection when user has no permissions', function (): void {
        expect($this->user->getAllPermissions())->toHaveCount(0);
    });

    test('does not return duplicates across roles', function (): void {
        $role2 = Role::query()->create(['name' => 'admin']);
        $this->user->assignRole('admin');

        $p1 = Permission::query()->create(['name' => 'post.view']);
        $this->role->grantPermission($p1);
        $role2->grantPermission($p1);

        $permissions = $this->user->getAllPermissions();

        expect($permissions)->toHaveCount(1);
    });
});
