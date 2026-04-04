---
layout: default
title: Permissions
nav_order: 3
---

# Permissions

> **Opt-in feature.** Permissions are completely optional. You can use Laravel Role Lite purely for role management without enabling the permission system.

The permission system lets you attach fine-grained permissions to roles. Permissions follow a `model.action` naming convention (e.g., `post.create`, `user.delete`) and are stored in the database. Lookups are accelerated by a two-tier in-memory and distributed cache.

---

## Setup

### 1. Publish permission migrations

Permission tables are published under a separate tag so you only add them when needed:

```bash
php artisan vendor:publish --tag=oltrematica-role-lite-permission-migrations
php artisan migrate
```

This creates two additional tables:

| Table | Purpose |
|-------|---------|
| `permissions` | Stores permission names and descriptions |
| `role_permission` | Pivot table linking permissions to roles |

### 2. Add HasPermissions to your model

`HasPermissions` depends on `HasRoles`, so add both traits:

```php
use Oltrematica\RoleLite\Trait\HasRoles;
use Oltrematica\RoleLite\Trait\HasPermissions;

class User extends Authenticatable
{
    use HasRoles, HasPermissions;
}
```

---

## Permission Naming Convention

Permissions follow the format `model_slug.action`, where the model slug is the snake_case class basename:

| Model class | Slug | Example permission |
|-------------|------|-------------------|
| `Post` | `post` | `post.create` |
| `ServiceVisit` | `service_visit` | `service_visit.view_any` |

You can use plain strings or a backed enum — anywhere a permission is accepted, both work.

```php
enum PostPermission: string
{
    case Create    = 'post.create';
    case Update    = 'post.update';
    case Delete    = 'post.delete';
    case ViewAny   = 'post.view_any';
}
```

---

## Creating Permissions

Use the static helpers on the `Permission` model to create permissions without writing SQL.

### Create all default actions for a model

```php
use Oltrematica\RoleLite\Models\Permission;

Permission::createForModel(Post::class);
// Creates: post.view_any, post.view, post.create, post.update,
//          post.delete, post.restore, post.force_delete,
//          post.delete_any, post.force_delete_any, post.restore_any
```

### Create a specific action for a model

```php
Permission::findOrCreateForModel(Post::class, 'publish');
// Creates (or returns existing): post.publish
```

### Build a permission name without creating it

```php
$name = Permission::buildPermissionName(Post::class, 'create');
// Returns: "post.create"
```

---

## Granting and Revoking via a User

These methods operate through the user's roles. Permissions are attached to roles, not directly to users.

### `givePermissionTo(string|BackedEnum $permission, ?Role $role = null)`

Grants the permission via the specified role, or via the user's **first role** if none is given. Creates the permission record if it does not exist.

```php
// Grant via the user's first role
$user->givePermissionTo('post.create');

// Grant via a specific role
$role = Role::where('name', 'editor')->first();
$user->givePermissionTo('post.create', $role);

// Using an enum
$user->givePermissionTo(PostPermission::Create);
```

### `revokePermissionTo(string|BackedEnum $permission, ?Role $role = null)`

Revokes the permission from the specified role, or from the user's first role.

```php
$user->revokePermissionTo('post.create');
$user->revokePermissionTo(PostPermission::Create, $role);
```

### `getAllPermissions()`

Returns all permissions the user holds across all of their roles.

```php
$permissions = $user->getAllPermissions();
// Returns: Collection<int, Permission>
```

---

## Granting and Revoking via a Role

You can also manage permissions directly on a `Role` model instance.

### `grantPermission(Permission $permission)`

Attaches the permission to the role. No-op if already granted. Automatically clears the permission cache.

```php
$role = Role::where('name', 'editor')->first();
$permission = Permission::findOrCreateForModel(Post::class, 'create');

$role->grantPermission($permission);
```

### `revokePermission(Permission $permission)`

Detaches the permission from the role. Automatically clears the permission cache.

```php
$role->revokePermission($permission);
```

### `syncPermissions(array $permissionIds)`

Replaces all permissions on the role with the given set (array of permission IDs). Automatically clears the permission cache.

```php
$permissionIds = Permission::whereIn('name', ['post.create', 'post.update'])->pluck('id')->all();
$role->syncPermissions($permissionIds);
```

### `hasPermission(string $permissionName): bool`

Checks whether the role has a specific permission (database query, not cached).

```php
$role->hasPermission('post.create'); // true / false
```

---

## Checking Permissions on a User

### `hasPermissionTo(string|BackedEnum $permission): bool`

Returns `true` if the user has the named permission through any of their roles.

```php
$user->hasPermissionTo('post.create');
$user->hasPermissionTo(PostPermission::Create);
```

### `hasAnyPermission(string|BackedEnum ...$permissions): bool`

Returns `true` if the user has **at least one** of the listed permissions. Returns `false` for an empty list.

```php
$user->hasAnyPermission('post.create', 'post.update');
```

### `hasAllPermissions(string|BackedEnum ...$permissions): bool`

Returns `true` if the user has **all** of the listed permissions. Returns `true` for an empty list.

```php
$user->hasAllPermissions('post.create', 'post.update', 'post.delete');
```

### `canDo(string $modelClass, string $action): bool`

Convenience method that builds the permission name from a model class and action, then checks it.

```php
$user->canDo(Post::class, 'create');     // checks "post.create"
$user->canDo(ServiceVisit::class, 'view_any'); // checks "service_visit.view_any"
```

---

## Permission Events

| Event | Fired when |
|-------|-----------|
| `Oltrematica\RoleLite\Events\PermissionGranted` | A permission is attached to a role |
| `Oltrematica\RoleLite\Events\PermissionRevoked` | A permission is detached from a role |

Each event receives the `PermissionRole` pivot model instance, which exposes `role_id` and `permission_id`.

```php
use Oltrematica\RoleLite\Events\PermissionGranted;

class LogPermissionChange
{
    public function handle(PermissionGranted $event): void
    {
        $roleId       = $event->permissionRole->role_id;
        $permissionId = $event->permissionRole->permission_id;
        // ...
    }
}
```

---

## Caching

The `PermissionService` uses a **two-tier cache** to keep permission checks fast:

1. **Distributed cache** — the entire role→permission map is stored as a single cache entry using your configured Laravel cache driver (Redis, Memcached, file, etc.). The default TTL is 3 600 seconds (1 hour).
2. **In-memory cache** — once the distributed cache is read within a request, the result is stored in a PHP array for the remainder of that request, avoiding repeated cache driver round-trips.

### Automatic invalidation

Whenever `grantPermission`, `revokePermission`, or `syncPermissions` is called on a `Role` model, the cache is cleared automatically — both tiers.

### Manual cache clearing

```php
use Oltrematica\RoleLite\Services\PermissionService;

app(PermissionService::class)->clearCache();
```

### Configuring the cache

In `config/oltrematica-role-lite.php`:

```php
'permissions' => [
    'cache_ttl'    => 3600,        // TTL in seconds; set to 0 to disable caching
    'cache_prefix' => 'role_lite', // Prefix for the cache key
],
```

Setting `cache_ttl` to `0` disables the distributed cache entirely; in-memory caching within a request still applies.

---

## Next Steps

- [Policy Integration](policies) — wire permissions into Laravel Policies
- [Configuration Reference](configuration) — table names, model names, default actions

---

[Home](index) | [Roles](roles) | **Permissions** | [Policies](policies) | [Configuration](configuration)
