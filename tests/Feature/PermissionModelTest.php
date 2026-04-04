<?php

declare(strict_types=1);

use Oltrematica\RoleLite\Models\Permission;
use Oltrematica\RoleLite\Models\Role;

describe('Permission model', function (): void {
    test('can be created with name', function (): void {
        $permission = Permission::query()->create(['name' => 'user.view']);
        expect($permission->name)->toBe('user.view')
            ->and($permission->exists)->toBeTrue();
    });

    test('can be created with name and description', function (): void {
        $permission = Permission::query()->create([
            'name' => 'user.create',
            'description' => 'Allows creating users',
        ]);
        expect($permission->name)->toBe('user.create')
            ->and($permission->description)->toBe('Allows creating users');
    });

    test('name must be unique', function (): void {
        Permission::query()->create(['name' => 'user.view']);
        Permission::query()->create(['name' => 'user.view']);
    })->throws(\Illuminate\Database\QueryException::class);

    test('roles relationship returns related roles', function (): void {
        $permission = Permission::query()->create(['name' => 'user.view']);
        $role = Role::query()->create(['name' => 'admin']);
        $permission->roles()->attach($role->id);
        expect($permission->roles)->toHaveCount(1)
            ->and($permission->roles->first()->name)->toBe('admin');
    });
});

describe('Permission static helpers', function (): void {
    test('getModelSlug converts class name to snake_case', function (): void {
        expect(Permission::getModelSlug('App\Models\Customer'))->toBe('customer')
            ->and(Permission::getModelSlug('App\Models\ServiceVisit'))->toBe('service_visit');
    });

    test('buildPermissionName creates model.action format', function (): void {
        expect(Permission::buildPermissionName('App\Models\Customer', 'view_any'))
            ->toBe('customer.view_any')
            ->and(Permission::buildPermissionName('App\Models\ServiceVisit', 'create'))
            ->toBe('service_visit.create');
    });

    test('findOrCreateForModel creates permission if not exists', function (): void {
        $permission = Permission::findOrCreateForModel('App\Models\Customer', 'view');
        expect($permission->name)->toBe('customer.view')
            ->and($permission->exists)->toBeTrue();
    });

    test('findOrCreateForModel returns existing permission', function (): void {
        $first = Permission::findOrCreateForModel('App\Models\Customer', 'view');
        $second = Permission::findOrCreateForModel('App\Models\Customer', 'view');
        expect($first->id)->toBe($second->id);
    });

    test('findOrCreateForModel stores description', function (): void {
        $permission = Permission::findOrCreateForModel('App\Models\Customer', 'view', 'View customers');
        expect($permission->description)->toBe('View customers');
    });

    test('createForModel creates all default action permissions for a model', function (): void {
        $permissions = Permission::createForModel('App\Models\Customer');
        expect($permissions)->toHaveCount(10)
            ->and($permissions->pluck('name')->toArray())->toContain(
                'customer.view_any',
                'customer.view',
                'customer.create',
                'customer.update',
                'customer.delete',
            );
    });

    test('createForModel accepts custom actions', function (): void {
        $permissions = Permission::createForModel('App\Models\Post', ['read', 'write', 'publish']);
        expect($permissions)->toHaveCount(3)
            ->and($permissions->pluck('name')->toArray())->toBe([
                'post.read',
                'post.write',
                'post.publish',
            ]);
    });

    test('createForModel skips already existing permissions', function (): void {
        Permission::query()->create(['name' => 'customer.view']);
        $permissions = Permission::createForModel('App\Models\Customer');
        expect($permissions)->toHaveCount(10);
        expect(Permission::query()->where('name', 'customer.view')->count())->toBe(1);
    });
});
