<?php

declare(strict_types=1);

use Oltrematica\RoleLite\Events\UserRoleCreated;
use Oltrematica\RoleLite\Events\UserRoleDeleted;
use Oltrematica\RoleLite\Models\Role;
use Oltrematica\RoleLite\Tests\TestModels\User;

test('when a roles has been assigned to a user UserRoleChanged event is fired', function (): void {
    // Arrange
    Event::fake();
    $user = User::query()->create(['name' => 'Test User', 'email' => 'test@email.com']);
    $role = Role::query()->create(['name' => 'Test Role']);

    // Act
    $user->assignRole($role->name);

    // Assert
    Event::assertDispatched(fn (UserRoleCreated $event): bool => $event->roleUser->user_id === $user->id
        && $event->roleUser->role_id === $role->id);
});

test('when a roles has been detached from a user UserRoleChanged event is fired', function (): void {
    // Arrange
    Event::fake();
    $user = User::query()->create(['name' => 'Test User', 'email' => 'test@email.com']);
    $role = Role::query()->create(['name' => 'Test Role']);

    // Act
    $user->removeRole($role->name);

    // Assert
    Event::assertDispatched(fn (UserRoleDeleted $event): bool => $event->roleUser->user_id === $user->id
        && $event->roleUser->role_id === $role->id);
});
