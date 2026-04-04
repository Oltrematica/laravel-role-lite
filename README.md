![GitHub Tests Action Status](https://github.com/Oltrematica/laravel-role-lite/actions/workflows/run-tests.yml/badge.svg)
![GitHub PhpStan Action Status](https://github.com/Oltrematica/laravel-role-lite/actions/workflows/phpstan.yml/badge.svg)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/oltrematica/laravel-role-lite.svg?style=flat-square)](https://packagist.org/packages/oltrematica/laravel-role-lite)
[![Total Downloads](https://img.shields.io/packagist/dt/oltrematica/laravel-role-lite.svg?style=flat-square)](https://packagist.org/packages/oltrematica/laravel-role-lite)


# Laravel Role Lite

A lightweight role and permission management package for Laravel applications.

Laravel Role Lite provides a simple, intuitive API for managing roles and permissions. Assign roles to users, attach permissions to roles, and check access throughout your application with minimal configuration. The permissions system is **opt-in**: if you only need roles, skip the permission migrations and the feature stays out of your way.

## Prerequisites

- Laravel v10, v11, v12, or v13
- PHP 8.3 or higher

## Installation

```bash
composer require oltrematica/laravel-role-lite
```

Publish and run the role migrations:

```bash
php artisan vendor:publish --tag=oltrematica-role-lite-migrations
php artisan migrate
```

**Optional — permissions system.** If you also want database-driven permissions, publish and run the permission migrations:

```bash
php artisan vendor:publish --tag=oltrematica-role-lite-permission-migrations
php artisan migrate
```

## Configuration

The default configuration works for most applications. To customise it, publish the config file:

```bash
php artisan vendor:publish --tag=oltrematica-role-lite-config
```

The published file lives at `config/oltrematica-role-lite.php`.

### Table Names

```php
'table_names' => [
    'roles'           => 'roles',       // roles table
    'users'           => 'users',       // your users table
    'role_user'       => 'role_user',   // role ↔ user pivot
    'permissions'     => 'permissions', // permissions table (opt-in)
    'role_permission' => 'role_permission', // role ↔ permission pivot (opt-in)
],
```

### Model Names

```php
'model_names' => [
    // Leave null to auto-detect from auth.providers.users.model
    'user' => null,
],
```

### Permissions Settings

```php
'permissions' => [
    'cache_ttl'       => 3600,        // seconds; set to 0 to disable caching
    'cache_prefix'    => 'role_lite', // prefix for all cache keys
    'default_actions' => [            // used by Permission::createForModel()
        'view_any', 'view', 'create', 'update', 'delete',
        'restore', 'force_delete', 'delete_any', 'force_delete_any', 'restore_any',
    ],
],
```

---

## Roles

### Setup

Add the `HasRoles` trait to your `User` model (or any Eloquent model):

```php
use Oltrematica\RoleLite\Trait\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

Using a `BackedEnum` for role names is recommended — it keeps role identifiers in one place and prevents typos:

```php
enum Role: string
{
    case ADMIN     = 'admin';
    case EDITOR    = 'editor';
    case MODERATOR = 'moderator';
}
```

### Assigning Roles

```php
// Assign a single role (string or BackedEnum)
$user->assignRole('admin');
$user->assignRole(Role::ADMIN);

// Sync replaces all current roles with the given set
$user->syncRoles('editor', 'moderator');
$user->syncRoles(Role::EDITOR, Role::MODERATOR);

// Remove a role
$user->removeRole('editor');
$user->removeRole(Role::EDITOR);
```

### Checking Roles

```php
// Exact match
$user->hasRole('admin');
$user->hasRole(Role::ADMIN);

// Has all of the given roles
$user->hasRoles('admin', 'editor');
$user->hasRoles(Role::ADMIN, Role::EDITOR);

// Has at least one of the given roles
$user->hasAnyRoles('admin', 'editor');
$user->hasAnyRoles(Role::ADMIN, Role::EDITOR);

// Convenience checks
$user->hasSomeRoles(); // true if user has at least one role
$user->hasNoRoles();   // true if user has no roles
```

---

## Permissions

Permissions are stored in the database and assigned to **roles** — not directly to users. A user gains a permission through any of their roles.

### Setup

Add `HasPermissions` to your `User` model. It requires `HasRoles` to also be present:

```php
use Oltrematica\RoleLite\Trait\HasRoles;
use Oltrematica\RoleLite\Trait\HasPermissions;

class User extends Authenticatable
{
    use HasRoles, HasPermissions;
}
```

### Creating Permissions

```php
use Oltrematica\RoleLite\Models\Permission;

// Create a permission with an arbitrary name
Permission::create(['name' => 'publish_posts']);

// Create (or find) a structured permission for a model class and action
// Name format: snake_case(ClassName).action → e.g. "blog_post.create"
$permission = Permission::findOrCreateForModel(BlogPost::class, 'create');
$permission = Permission::findOrCreateForModel(BlogPost::class, 'create', 'Can create blog posts');

// Create the full default action set for a model in one call
// Produces: blog_post.view_any, blog_post.view, blog_post.create, blog_post.update, etc.
$permissions = Permission::createForModel(BlogPost::class);

// Pass a custom action list
$permissions = Permission::createForModel(BlogPost::class, ['view', 'create', 'update']);
```

### Granting and Revoking Permissions

Permissions are granted/revoked at the **role** level:

```php
use Oltrematica\RoleLite\Models\Role;
use Oltrematica\RoleLite\Models\Permission;

$role       = Role::where('name', 'editor')->first();
$permission = Permission::where('name', 'blog_post.create')->first();

$role->grantPermission($permission);
$role->revokePermission($permission);

// Replace all permissions for a role at once
$role->syncPermissions([$permissionA->id, $permissionB->id]);
```

You can also grant/revoke via the user, which targets the user's first role by default or a specific role:

```php
// Grant through the user's first role
$user->givePermissionTo('publish_posts');
$user->givePermissionTo(PostPermission::PUBLISH);

// Grant through a specific role
$editorRole = Role::where('name', 'editor')->first();
$user->givePermissionTo('publish_posts', $editorRole);

// Revoke
$user->revokePermissionTo('publish_posts');
$user->revokePermissionTo('publish_posts', $editorRole);
```

### Checking Permissions

```php
// Single permission
$user->hasPermissionTo('blog_post.create');
$user->hasPermissionTo(PostPermission::CREATE);

// Any of the given permissions
$user->hasAnyPermission('blog_post.view', 'blog_post.create');

// All of the given permissions
$user->hasAllPermissions('blog_post.view', 'blog_post.create');

// Model + action shorthand (builds "blog_post.create" internally)
$user->canDo(BlogPost::class, 'create');

// Retrieve all permissions granted to the user (across all their roles)
$permissions = $user->getAllPermissions();
```

### Using Permissions in Laravel Policies

The `ChecksPermissions` trait simplifies writing database-driven policies. It automatically derives the model name from the policy class name (`CustomerPolicy` → `customer`, `ServiceVisitPolicy` → `service_visit`):

```php
use Oltrematica\RoleLite\Trait\ChecksPermissions;

class CustomerPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'view_any');
        // checks: customer.view_any
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create');
        // checks: customer.create
    }
}
```

If the model class cannot be inferred from the policy name, pass it explicitly:

```php
public function viewAny(User $user): bool
{
    return $this->checkPermission($user, 'view_any', Customer::class);
}
```

You can also resolve the permission name without performing a check:

```php
$name = $this->getPermissionName('create');
// → "customer.create"

$name = $this->getPermissionName('create', Order::class);
// → "order.create"
```

### PermissionService and Cache Management

`PermissionService` is a singleton that loads all role-permission mappings into a two-tier cache (in-memory + distributed). This means permission checks within the same request never hit the database more than once.

The cache is cleared automatically whenever you call `grantPermission`, `revokePermission`, or `syncPermissions` on a role.

If you need to clear it manually — for example after bulk database operations:

```php
use Oltrematica\RoleLite\Services\PermissionService;

app(PermissionService::class)->clearCache();
```

Configure caching behaviour in `config/oltrematica-role-lite.php`:

```php
'permissions' => [
    'cache_ttl'    => 3600,        // TTL in seconds; 0 disables the distributed cache
    'cache_prefix' => 'role_lite', // prefix for cache keys
],
```

---

## Events

The package fires events for role and permission changes that you can listen to in your application.

### Role Events

| Event | Fired when |
|---|---|
| `Oltrematica\RoleLite\Events\UserRoleCreated` | A role is assigned to a user |
| `Oltrematica\RoleLite\Events\UserRoleDeleted` | A role is removed from a user |
| `Oltrematica\RoleLite\Events\UserRoleUpdated` | A role assignment is updated |

### Permission Events

| Event | Fired when |
|---|---|
| `Oltrematica\RoleLite\Events\PermissionGranted` | A permission is granted to a role |
| `Oltrematica\RoleLite\Events\PermissionRevoked` | A permission is revoked from a role |

Both permission events receive a `PermissionRole` pivot model with `role_id` and `permission_id` properties.

---

## Code Quality

### Rector

```shell
composer refactor
```

### PhpStan

```shell
composer analyse
```

### Pint

```shell
composer format
```

### Automated Tests

```shell
composer test
```

---

## Contributing

Feel free to contribute by submitting issues or pull requests. We welcome any improvements or bug fixes.
