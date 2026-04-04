<?php

declare(strict_types=1);

use Oltrematica\RoleLite\Events\PermissionGranted;
use Oltrematica\RoleLite\Events\PermissionRevoked;
use Oltrematica\RoleLite\Models\Permission;
use Oltrematica\RoleLite\Models\Role;

test('granting a permission to a role fires PermissionGranted event', function (): void {
    Event::fake();
    $role = Role::query()->create(['name' => 'admin']);
    $permission = Permission::query()->create(['name' => 'user.view']);

    $permission->roles()->attach($role->id);

    Event::assertDispatched(fn (PermissionGranted $event): bool => $event->permissionRole->role_id === $role->id
        && $event->permissionRole->permission_id === $permission->id);
});

test('revoking a permission from a role fires PermissionRevoked event', function (): void {
    Event::fake();
    $role = Role::query()->create(['name' => 'admin']);
    $permission = Permission::query()->create(['name' => 'user.view']);

    $permission->roles()->attach($role->id);
    $permission->roles()->detach($role->id);

    Event::assertDispatchedTimes(PermissionGranted::class, 1);
    Event::assertDispatchedTimes(PermissionRevoked::class, 1);
});
