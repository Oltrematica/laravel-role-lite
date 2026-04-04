<?php

declare(strict_types=1);

use Oltrematica\RoleLite\Models\Permission;
use Oltrematica\RoleLite\Models\Role;
use Oltrematica\RoleLite\Services\PermissionService;
use Oltrematica\RoleLite\Tests\TestModels\User;
use Oltrematica\RoleLite\Trait\ChecksPermissions;

// Create fake policy classes for testing
class CustomerPolicy
{
    use ChecksPermissions;
}

class ServiceVisitPolicy
{
    use ChecksPermissions;
}

beforeEach(function (): void {
    $this->user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
    $this->role = Role::query()->create(['name' => 'editor']);
    $this->user->assignRole('editor');
    app(PermissionService::class)->clearCache();
});

describe('getModelClass', function (): void {
    test('derives model class from policy name', function (): void {
        $policy = new CustomerPolicy;
        $reflection = new ReflectionMethod($policy, 'getModelClass');

        expect($reflection->invoke($policy))->toBe('Customer');
    });

    test('handles multi-word policy names', function (): void {
        $policy = new ServiceVisitPolicy;
        $reflection = new ReflectionMethod($policy, 'getModelClass');

        expect($reflection->invoke($policy))->toBe('ServiceVisit');
    });
});

describe('checkPermission', function (): void {
    test('returns true when user has permission for derived model', function (): void {
        $permission = Permission::query()->create(['name' => 'customer.view']);
        $this->role->grantPermission($permission);
        app(PermissionService::class)->clearCache();

        $policy = new CustomerPolicy;
        $reflection = new ReflectionMethod($policy, 'checkPermission');

        expect($reflection->invoke($policy, $this->user, 'view'))->toBeTrue();
    });

    test('returns false when user lacks permission', function (): void {
        $policy = new CustomerPolicy;
        $reflection = new ReflectionMethod($policy, 'checkPermission');

        expect($reflection->invoke($policy, $this->user, 'delete'))->toBeFalse();
    });

    test('uses custom model class when provided', function (): void {
        $permission = Permission::query()->create(['name' => 'order.create']);
        $this->role->grantPermission($permission);
        app(PermissionService::class)->clearCache();

        $policy = new CustomerPolicy;
        $reflection = new ReflectionMethod($policy, 'checkPermission');

        expect($reflection->invoke($policy, $this->user, 'create', 'Order'))->toBeTrue();
    });
});

describe('getPermissionName', function (): void {
    test('builds permission name from action and derived model', function (): void {
        $policy = new CustomerPolicy;
        $reflection = new ReflectionMethod($policy, 'getPermissionName');

        expect($reflection->invoke($policy, 'view_any'))->toBe('customer.view_any');
    });

    test('builds permission name from action and custom model', function (): void {
        $policy = new CustomerPolicy;
        $reflection = new ReflectionMethod($policy, 'getPermissionName');

        expect($reflection->invoke($policy, 'create', 'ServiceVisit'))->toBe('service_visit.create');
    });
});
