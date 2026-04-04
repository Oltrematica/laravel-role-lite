<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Oltrematica\RoleLite\Models\Role;
use Oltrematica\RoleLite\Tests\TestModels\User;

describe('package works without permission tables', function (): void {

    beforeEach(function (): void {
        // Drop permission tables to simulate user who didn't publish permission migrations
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
    });

    test('roles still work without permissions table', function (): void {
        $user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
        $user->assignRole('admin');

        expect($user->hasRole('admin'))->toBeTrue()
            ->and($user->roles)->toHaveCount(1);
    });

    test('hasRoles still works', function (): void {
        $user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
        $user->assignRole('admin');
        $user->assignRole('editor');

        expect($user->hasRoles('admin', 'editor'))->toBeTrue();
    });

    test('hasAnyRoles still works', function (): void {
        $user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
        $user->assignRole('editor');

        expect($user->hasAnyRoles('admin', 'editor'))->toBeTrue();
    });

    test('syncRoles still works', function (): void {
        $user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
        $user->assignRole('old-role');
        $user->syncRoles('new-role');

        expect($user->hasRole('new-role'))->toBeTrue()
            ->and($user->hasRole('old-role'))->toBeFalse();
    });

    test('removeRole still works', function (): void {
        $user = User::query()->create(['name' => 'Test', 'email' => 'test@test.com']);
        $user->assignRole('temp');
        $user->removeRole('temp');

        expect($user->hasNoRoles())->toBeTrue();
    });

    test('Role model can be created and queried without permissions table', function (): void {
        $role = Role::query()->create(['name' => 'admin']);

        expect($role->exists)->toBeTrue()
            ->and(Role::query()->where('name', 'admin')->exists())->toBeTrue();
    });
});
