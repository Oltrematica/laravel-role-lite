---
layout: default
title: Roles
nav_order: 2
---

# Roles

Role management is the core feature of Laravel Role Lite. Roles are stored in the database and can be assigned to any Eloquent model using either a plain string or a PHP backed enum.

---

## Installation

### 1. Install the package

```bash
composer require oltrematica/laravel-role-lite
```

The service provider is auto-discovered by Laravel.

### 2. Publish and run migrations

```bash
php artisan vendor:publish --tag=oltrematica-role-lite-migrations
php artisan migrate
```

This creates two tables:

| Table | Purpose |
|-------|---------|
| `roles` | Stores role names |
| `role_user` | Pivot table linking roles to users (or any model) |

### 3. Publish the config (optional)

```bash
php artisan vendor:publish --tag=oltrematica-role-lite-config
```

This publishes `config/oltrematica-role-lite.php`.

---

## Adding the Trait

Add `HasRoles` to your User model (or any Eloquent model):

```php
use Oltrematica\RoleLite\Trait\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

---

## Defining Roles with Enums

The recommended approach is to define roles as a PHP backed enum. This provides IDE autocompletion and prevents typos:

```php
enum UserRole: string
{
    case Admin   = 'admin';
    case Editor  = 'editor';
    case Viewer  = 'viewer';
}
```

You can also pass plain strings anywhere a role is expected.

---

## Assigning Roles

### `assignRole(string|BackedEnum $role)`

Assigns a single role to the model. Creates the role record if it does not exist. No-op if the user already has the role.

```php
$user->assignRole(UserRole::Admin);
$user->assignRole('editor');
```

### `syncRoles(string|BackedEnum ...$roles)`

Replaces all of the model's current roles with the given set. Roles not in the list are detached; new roles are created if needed.

```php
$user->syncRoles(UserRole::Editor, UserRole::Viewer);

// Remove all roles
$user->syncRoles();
```

---

## Removing Roles

### `removeRole(string|BackedEnum $role)`

Detaches a single role from the model. No-op if the user does not have the role.

```php
$user->removeRole(UserRole::Admin);
```

---

## Checking Roles

### `hasRole(string|BackedEnum $role): bool`

Returns `true` if the model has the given role.

```php
$user->hasRole(UserRole::Admin);   // true / false
$user->hasRole('admin');           // also works
```

### `hasRoles(string|BackedEnum ...$roles): bool`

Returns `true` if the model has **all** of the given roles. Passing no arguments returns `true`.

```php
$user->hasRoles(UserRole::Admin, UserRole::Editor); // must have both
```

### `hasAnyRoles(string|BackedEnum ...$roles): bool`

Returns `true` if the model has **at least one** of the given roles. Passing no arguments returns `false`.

```php
$user->hasAnyRoles(UserRole::Admin, UserRole::Editor); // either one
```

### `hasSomeRoles(): bool`

Returns `true` if the model has at least one role assigned.

```php
if ($user->hasSomeRoles()) {
    // user has at least one role
}
```

### `hasNoRoles(): bool`

Returns `true` if the model has no roles assigned.

```php
if ($user->hasNoRoles()) {
    // user is role-less
}
```

---

## Accessing the Roles Relationship

The `roles` property is a standard Eloquent `BelongsToMany` relationship, so you can use it directly:

```php
// Eager load roles
$user = User::with('roles')->find($id);

// Get role names
$roleNames = $user->roles->pluck('name');
```

---

## Role Events

Laravel Role Lite dispatches events on the `RoleUser` pivot model (which uses Eloquent observers). Listen to these in your `EventServiceProvider` or using `#[AsEventListener]`:

| Event | Fired when |
|-------|-----------|
| `Oltrematica\RoleLite\Events\UserRoleCreated` | A role is assigned to a user |
| `Oltrematica\RoleLite\Events\UserRoleDeleted` | A role is removed from a user |
| `Oltrematica\RoleLite\Events\UserRoleUpdated` | A role-user record is updated |

Each event receives the `RoleUser` pivot model instance, which exposes `user_id` and `role_id`.

```php
use Oltrematica\RoleLite\Events\UserRoleCreated;

class SendWelcomeEmailOnRoleAssignment
{
    public function handle(UserRoleCreated $event): void
    {
        $userId = $event->roleUser->user_id;
        $roleId = $event->roleUser->role_id;
        // ...
    }
}
```

---

## Next Steps

- [Permissions](permissions) — add database-driven permissions to roles
- [Configuration Reference](configuration) — customise table names and the user model

---

[Home](index) | **Roles** | [Permissions](permissions) | [Policies](policies) | [Configuration](configuration)
