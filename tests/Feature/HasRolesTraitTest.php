<?php

declare(strict_types=1);

use Oltrematica\RoleLite\Models\Role;
use Oltrematica\RoleLite\Tests\TestModels\TestRoleEnum;
use Oltrematica\RoleLite\Tests\TestModels\User;

/**
 * Tests for the roles() relationship.
 */
describe('roles relationship', function (): void {
    test('a user can have multiple roles and they can be accessed', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'User With Roles', 'email' => 'userwithroles@example.com']);
        $roleAdmin = Role::query()->firstOrCreate(['name' => 'Admin']);
        $roleEditor = Role::query()->firstOrCreate(['name' => 'Editor']);
        $user->roles()->attach([$roleAdmin->id, $roleEditor->id]);

        // Act
        $userRoles = $user->roles;

        // Assert
        expect($userRoles)->toHaveCount(2)
            ->and($userRoles->pluck('name')->all())->toEqualCanonicalizing(['Admin', 'Editor']);
    });

    test('returns an empty collection for a user with no roles', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'User Without Roles', 'email' => 'userwithoutroles@example.com']);

        // Act
        $userRoles = $user->roles;

        // Assert
        expect($userRoles)->toBeEmpty();
    });
});

/**
 * Tests for hasRole() method.
 */
describe('hasRole method', function (): void {
    // Arrange: Common setup for hasRole tests moved to beforeEach
    beforeEach(function (): void {
        $this->user = User::query()->create(['name' => 'Test User', 'email' => 'testuser@example.com']);
        $this->adminRole = Role::query()->firstOrCreate(['name' => 'Administrator']);
        $this->editorRoleEnum = TestRoleEnum::EDITOR;
        $this->editorRoleModel = Role::query()->firstOrCreate(['name' => $this->editorRoleEnum->value]);

        $this->user->assignRole($this->adminRole->name);
        $this->user->assignRole($this->editorRoleEnum);
    });

    test('returns true when user has the role (string)', function (): void {
        // Act & Assert
        expect($this->user->hasRole($this->adminRole->name))->toBeTrue();
    });

    test('returns false when user does not have the role (string)', function (): void {
        // Act & Assert
        expect($this->user->hasRole('NonExistentRole'))->toBeFalse();
    });

    test('returns true when user has the role (BackedEnum)', function (): void {
        // Act & Assert
        expect($this->user->hasRole($this->editorRoleEnum))->toBeTrue();
    });

    test('returns false when user does not have the role (BackedEnum)', function (): void {
        // Act & Assert
        expect($this->user->hasRole(TestRoleEnum::VIEWER))->toBeFalse();
    });

    test('returns false for a user with no roles when checking a role', function (): void {
        // Arrange
        $newUser = User::query()->create(['name' => 'New User', 'email' => 'newuser@example.com']);

        // Act & Assert
        expect($newUser->hasRole('AnyRole'))->toBeFalse();
    });
});

/**
 * Tests for hasRoles() method.
 */
describe('hasRoles method', function (): void {
    // Arrange: Common setup for hasRoles tests moved to beforeEach
    beforeEach(function (): void {
        $this->user = User::query()->create(['name' => 'User For HasRoles', 'email' => 'userforhasroles@example.com']);
        $this->roleDev = Role::query()->firstOrCreate(['name' => 'Developer']);
        $this->roleTesterEnum = TestRoleEnum::VIEWER;
        $this->roleTester = Role::query()->firstOrCreate(['name' => $this->roleTesterEnum->value]);
        $this->roleManager = Role::query()->firstOrCreate(['name' => 'Manager']);

        $this->user->assignRole($this->roleDev->name);
        $this->user->assignRole($this->roleTesterEnum);
    });

    test('returns true when user has all specified string roles', function (): void {
        // Act & Assert
        expect($this->user->hasRoles($this->roleDev->name, $this->roleTester->name))->toBeTrue();
    });

    test('returns false when user has some but not all specified string roles', function (): void {
        // Act & Assert
        expect($this->user->hasRoles($this->roleDev->name, $this->roleManager->name))->toBeFalse();
    });

    test('returns false when user has none of the specified string roles', function (): void {
        // Act & Assert
        expect($this->user->hasRoles('Support', $this->roleManager->name))->toBeFalse();
    });

    test('returns true when no roles are passed', function (): void {
        // Act & Assert
        expect($this->user->hasRoles())->toBeTrue();
    });

    test('returns true when user has all specified BackedEnum roles', function (): void {
        // Arrange: Assign another enum role for a multi-enum check
        $this->user->assignRole(TestRoleEnum::ADMIN); // ADMIN = 'admin-enum-role'
        $adminEnumRole = TestRoleEnum::ADMIN;

        // Act & Assert
        expect($this->user->hasRoles($this->roleTesterEnum, $adminEnumRole))->toBeTrue();
        // Cleanup the added role for other tests in this describe block if necessary, or re-initialize user.
        // For simplicity here, assuming tests are isolated or this is the last enum test for this user state.
        $this->user->removeRole(TestRoleEnum::ADMIN); // Clean up
    });

    test('returns true when user has all specified mixed string and BackedEnum roles', function (): void {
        // Act & Assert
        expect($this->user->hasRoles($this->roleDev->name, $this->roleTesterEnum))->toBeTrue();
    });

    test('handles duplicate roles in arguments gracefully', function (): void {
        // Act & Assert
        expect($this->user->hasRoles($this->roleDev->name, $this->roleTesterEnum, $this->roleDev->name, $this->roleTesterEnum))->toBeTrue();
    });

    test('returns false for a user with no roles when roles are specified', function (): void {
        // Arrange
        $newUser = User::query()->create(['name' => 'New User For HasRoles', 'email' => 'newuserhr@example.com']);

        // Act & Assert
        expect($newUser->hasRoles('AnyRole', 'AnotherRole'))->toBeFalse();
    });
});

/**
 * Tests for hasAnyRoles() method.
 */
describe('hasAnyRoles method', function (): void {
    // Arrange: Common setup for hasAnyRoles tests moved to beforeEach
    beforeEach(function (): void {
        $this->user = User::query()->create(['name' => 'User For HasAnyRoles', 'email' => 'userforhasanyroles@example.com']);
        $this->roleSupport = Role::query()->firstOrCreate(['name' => 'SupportStaff']);
        $this->roleLeadEnum = TestRoleEnum::ADMIN; // ADMIN = 'admin-enum-role'
        // $this->roleLead = Role::query()->firstOrCreate(['name' => $this->roleLeadEnum->value]); // This variable was unused

        $this->user->assignRole($this->roleSupport->name);
    });

    test('returns true when user has at least one of the specified string roles', function (): void {
        // Act & Assert
        expect($this->user->hasAnyRoles($this->roleSupport->name, 'NonExistentRoleForUser'))->toBeTrue();
    });

    test('returns false when user has none of the specified string roles', function (): void {
        // Act & Assert
        expect($this->user->hasAnyRoles('Sales', 'Marketing'))->toBeFalse();
    });

    test('returns false when no roles are passed', function (): void {
        // Act & Assert
        expect($this->user->hasAnyRoles())->toBeFalse();
    });

    test('returns true when user has at least one of the specified BackedEnum roles', function (): void {
        // Arrange: Assign the enum role to the user
        $this->user->assignRole($this->roleLeadEnum);

        // Act & Assert
        expect($this->user->hasAnyRoles(TestRoleEnum::EDITOR, $this->roleLeadEnum))->toBeTrue();

        // Clean up
        $this->user->removeRole($this->roleLeadEnum);
    });

    test('returns true when user has at least one of the specified mixed string and BackedEnum roles', function (): void {
        // Act & Assert
        expect($this->user->hasAnyRoles($this->roleSupport->name, $this->roleLeadEnum, 'AnotherNonExistentRole'))->toBeTrue();
    });

    test('returns false for a user with no roles when roles are specified', function (): void {
        // Arrange
        $newUser = User::query()->create(['name' => 'New User For HasAnyRoles', 'email' => 'newuserhar@example.com']);

        // Act & Assert
        expect($newUser->hasAnyRoles('AnyRole', 'AnotherRole'))->toBeFalse();
    });
});

/**
 * Tests for hasSomeRoles() and hasNoRoles() methods.
 */
describe('hasSomeRoles and hasNoRoles methods', function (): void {
    test('hasSomeRoles returns true when user has roles, hasNoRoles returns false', function (): void {
        // Arrange
        $userWithRoles = User::query()->create(['name' => 'User With Some Roles', 'email' => 'usersomeroles@example.com']);
        $userWithRoles->assignRole('TemporaryRole');

        // Act & Assert
        expect($userWithRoles->hasSomeRoles())->toBeTrue()
            ->and($userWithRoles->hasNoRoles())->toBeFalse();
    });

    test('hasSomeRoles returns false when user has no roles, hasNoRoles returns true', function (): void {
        // Arrange
        $userWithoutRoles = User::query()->create(['name' => 'User With No Roles At All', 'email' => 'usernoroles@example.com']);

        // Act & Assert
        expect($userWithoutRoles->hasSomeRoles())->toBeFalse()
            ->and($userWithoutRoles->hasNoRoles())->toBeTrue();
    });
});

/**
 * Tests for assignRole() method (focus on state and enum usage).
 */
describe('assignRole method - state and enum usage', function (): void {
    test('assigning a string role correctly adds it to the user', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'Assign Test User', 'email' => 'assigntest@example.com']);
        $roleName = 'Finance';

        // Act
        $user->assignRole($roleName);

        // Assert
        expect($user->hasRole($roleName))->toBeTrue();
        $roleModel = Role::query()->where('name', $roleName)->first();
        expect($roleModel)->not->toBeNull(); // Ensure role was created in roles table
    });

    test('assigning a BackedEnum role correctly adds it to the user', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'Assign Enum User', 'email' => 'assignenum@example.com']);
        $enumRole = TestRoleEnum::EDITOR; // EDITOR = 'editor-enum-role'

        // Act
        $user->assignRole($enumRole);

        // Assert
        expect($user->hasRole($enumRole->value))->toBeTrue()
            ->and($user->hasRole($enumRole))->toBeTrue(); // Check with enum itself
        $roleModel = Role::query()->where('name', $enumRole->value)->first();
        expect($roleModel)->not->toBeNull();
    });

    test('assigning an existing role does not duplicate it', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'Assign Existing User', 'email' => 'assignexisting@example.com']);
        $roleName = 'HR';
        $user->assignRole($roleName); // First assignment
        $rolesCountBefore = $user->roles()->count();

        // Act
        $user->assignRole($roleName); // Second assignment of the same role

        // Assert
        expect($user->roles()->count())->toBe($rolesCountBefore)
            ->and($user->hasRole($roleName))->toBeTrue();
    });
});

/**
 * Tests for removeRole() method (focus on state and enum usage).
 */
describe('removeRole method - state and enum usage', function (): void {
    test('removing a string role correctly removes it from the user', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'Remove Test User', 'email' => 'removetest@example.com']);
        $roleName = 'Operations';
        $user->assignRole($roleName);
        expect($user->hasRole($roleName))->toBeTrue(); // Pre-condition

        // Act
        $user->removeRole($roleName);

        // Assert
        expect($user->hasRole($roleName))->toBeFalse();
    });

    test('removing a BackedEnum role correctly removes it from the user', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'Remove Enum User', 'email' => 'removeenum@example.com']);
        $enumRole = TestRoleEnum::VIEWER; // VIEWER = 'viewer-enum-role'
        $user->assignRole($enumRole);
        expect($user->hasRole($enumRole))->toBeTrue(); // Pre-condition

        // Act
        $user->removeRole($enumRole);

        // Assert
        expect($user->hasRole($enumRole))->toBeFalse();
    });

    test('removing a non-existent role from user does not cause errors and state remains unchanged', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'Remove NonExistent User', 'email' => 'removenonexistent@example.com']);
        $user->assignRole('ExistingRole');
        $rolesCountBefore = $user->roles()->count();

        // Act
        $user->removeRole('RoleTheyDontHave');

        // Assert
        expect($user->roles()->count())->toBe($rolesCountBefore)
            ->and($user->hasRole('RoleTheyDontHave'))->toBeFalse();
    });
});

/**
 * Tests for syncRoles() method (focus on state and enum usage).
 */
describe('syncRoles method - state and enum usage', function (): void {
    test('syncing with string roles correctly updates user roles', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'Sync String User', 'email' => 'syncstring@example.com']);
        $user->assignRole('OldRole1');
        $user->assignRole('OldRole2');

        // Act
        $user->syncRoles('NewRole1', 'NewRole2');

        // Assert
        expect($user->hasRole('NewRole1'))->toBeTrue()
            ->and($user->hasRole('NewRole2'))->toBeTrue()
            ->and($user->hasRole('OldRole1'))->toBeFalse()
            ->and($user->hasRole('OldRole2'))->toBeFalse()
            ->and($user->roles()->count())->toBe(2);
    });

    test('syncing with BackedEnum roles correctly updates user roles', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'Sync Enum User', 'email' => 'syncenum@example.com']);
        $user->assignRole('SomeStringRole');
        $user->assignRole(TestRoleEnum::ADMIN);

        // Act
        $user->syncRoles(TestRoleEnum::EDITOR, TestRoleEnum::VIEWER);

        // Assert
        expect($user->hasRole(TestRoleEnum::EDITOR))->toBeTrue()
            ->and($user->hasRole(TestRoleEnum::VIEWER))->toBeTrue()
            ->and($user->hasRole(TestRoleEnum::ADMIN))->toBeFalse()
            ->and($user->hasRole('SomeStringRole'))->toBeFalse()
            ->and($user->roles()->count())->toBe(2);
    });

    test('syncing with a mix of string and BackedEnum roles', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'Sync Mixed User', 'email' => 'syncmixed@example.com']);
        $user->assignRole('InitialRole');
        $user->assignRole(TestRoleEnum::ADMIN);

        // Act
        $user->syncRoles('FinalStringRole', TestRoleEnum::EDITOR);

        // Assert
        expect($user->hasRole('FinalStringRole'))->toBeTrue()
            ->and($user->hasRole(TestRoleEnum::EDITOR))->toBeTrue()
            ->and($user->hasRole('InitialRole'))->toBeFalse()
            ->and($user->hasRole(TestRoleEnum::ADMIN))->toBeFalse()
            ->and($user->roles()->count())->toBe(2);
    });

    test('syncing with an empty list removes all roles', function (): void {
        // Arrange
        $user = User::query()->create(['name' => 'Sync Empty User', 'email' => 'syncempty@example.com']);
        $user->assignRole('RoleA');
        $user->assignRole(TestRoleEnum::VIEWER);

        // Act
        $user->syncRoles();

        // Assert
        expect($user->hasNoRoles())->toBeTrue()
            ->and($user->roles()->count())->toBe(0);
    });
});
