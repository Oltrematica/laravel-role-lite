<?php

declare(strict_types=1);

use Oltrematica\RoleLite\Events\UserRoleCreated;
use Oltrematica\RoleLite\Events\UserRoleDeleted;
use Oltrematica\RoleLite\Models\Role;
use Oltrematica\RoleLite\Tests\TestModels\User;

test('when a role is assigned to a user UserRoleCreated event is fired', function (): void { // Title slightly rephrased for clarity
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

test('when a role is detached from a user UserRoleDeleted event is fired', function (): void { // Title rephrased for clarity
    // Arrange
    Event::fake(); // Moved to the beginning of Arrange block and fakes all events by default
    $user = User::query()->create(['name' => 'Test User Detach', 'email' => 'test-detach@email.com']);
    $role = Role::query()->create(['name' => 'Test Role Detach']);

    // Assign the role first. This will dispatch UserRoleCreated.
    $user->assignRole($role->name);

    // Act: Remove the role. This should dispatch UserRoleDeleted.
    $user->removeRole($role->name);

    // Assert:
    // 1. UserRoleCreated was dispatched once (by assignRole).
    Event::assertDispatchedTimes(UserRoleCreated::class, 1);
    // 2. UserRoleDeleted was dispatched once (by removeRole).
    Event::assertDispatchedTimes(UserRoleDeleted::class, 1);

    // 3. Verify the contents of the dispatched UserRoleDeleted event.
    Event::assertDispatched(fn (UserRoleDeleted $event): bool => $event->roleUser->user_id === $user->id
        && $event->roleUser->role_id === $role->id);

    // 4. Verify the contents of the dispatched UserRoleCreated event (for completeness).
    Event::assertDispatched(fn (UserRoleCreated $event): bool => $event->roleUser->user_id === $user->id
        && $event->roleUser->role_id === $role->id);
});
