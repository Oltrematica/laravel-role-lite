---
layout: default
title: Configuration
nav_order: 5
---

# Configuration Reference

Publish the configuration file to customise table names, model names, and permission cache behaviour:

```bash
php artisan vendor:publish --tag=oltrematica-role-lite-config
```

This creates `config/oltrematica-role-lite.php`.

---

## Table Names

All table names are configurable. Change them before running migrations if your application uses a different naming convention.

```php
'table_names' => [
    'roles'           => 'roles',
    'users'           => 'users',
    'role_user'       => 'role_user',
    'permissions'     => 'permissions',
    'role_permission' => 'role_permission',
],
```

| Key | Default | Description |
|-----|---------|-------------|
| `roles` | `roles` | Stores role records |
| `users` | `users` | Your application's user table (used in the `role_user` pivot) |
| `role_user` | `role_user` | Pivot table linking roles to users |
| `permissions` | `permissions` | Stores permission records (opt-in feature) |
| `role_permission` | `role_permission` | Pivot table linking permissions to roles (opt-in feature) |

> If you change `users`, make sure it matches the table your user model uses, otherwise the `role_user` foreign key will reference the wrong table.

---

## Model Names

```php
'model_names' => [
    'user' => null,
],
```

| Key | Default | Description |
|-----|---------|-------------|
| `user` | `null` | FQCN of your user model. When `null`, the package reads `config('auth.providers.users.model')` automatically |

Override this only if your user model is not registered as the default auth provider:

```php
'model_names' => [
    'user' => \App\Models\Admin::class,
],
```

---

## Permission Settings

These settings apply to the opt-in [permission system](permissions).

```php
'permissions' => [
    'cache_ttl'      => 3600,
    'cache_prefix'   => 'role_lite',
    'default_actions' => [
        'view_any',
        'view',
        'create',
        'update',
        'delete',
        'restore',
        'force_delete',
        'delete_any',
        'force_delete_any',
        'restore_any',
    ],
],
```

### `cache_ttl`

| Default | `3600` (seconds) |
|---------|-----------------|
| Type | `int` |

Time-to-live for the distributed permissions cache (e.g., Redis, file). The entire role→permission map is cached as a single entry for this duration.

Set to `0` to disable the distributed cache. In-memory caching within a single request still applies regardless of this setting.

### `cache_prefix`

| Default | `role_lite` |
|---------|------------|
| Type | `string` |

Prefix prepended to the permission cache key. The full key is `{cache_prefix}_role_permissions`. Change this if you need to namespace cache keys across multiple applications sharing the same cache store.

### `default_actions`

| Default | `view_any`, `view`, `create`, `update`, `delete`, `restore`, `force_delete`, `delete_any`, `force_delete_any`, `restore_any` |
|---------|------------|
| Type | `array<string>` |

The set of actions created when calling `Permission::createForModel(SomeModel::class)` without an explicit actions list. These names follow Laravel Filament/Policy conventions.

Customise to match your application's action vocabulary:

```php
'default_actions' => [
    'view',
    'create',
    'update',
    'delete',
],
```

---

## Full Default Configuration

```php
<?php

return [

    'table_names' => [
        'roles'           => 'roles',
        'users'           => 'users',
        'role_user'       => 'role_user',
        'permissions'     => 'permissions',
        'role_permission' => 'role_permission',
    ],

    'permissions' => [
        'cache_ttl'    => 3600,
        'cache_prefix' => 'role_lite',
        'default_actions' => [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force_delete',
            'delete_any',
            'force_delete_any',
            'restore_any',
        ],
    ],

    'model_names' => [
        'user' => null,
    ],
];
```

---

[Home](index) | [Roles](roles) | [Permissions](permissions) | [Policies](policies) | **Configuration**
