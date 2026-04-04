---
layout: default
title: Policy Integration
nav_order: 4
---

# Policy Integration

> **Opt-in feature.** Policy integration requires the [permission system](permissions) to be enabled.

The `ChecksPermissions` trait provides a clean bridge between Laravel's built-in Policy system and the database-driven permission system in Laravel Role Lite. Drop it into any Policy class and your policy methods become one-liners.

---

## Adding ChecksPermissions to a Policy

```php
use Oltrematica\RoleLite\Trait\ChecksPermissions;

class PostPolicy
{
    use ChecksPermissions;
}
```

That's it. The trait provides the `checkPermission` helper, and it automatically derives the model name from the policy class name.

---

## Auto Model Class Derivation

The trait strips `Policy` from the end of the class name and snake_cases the result to produce the model slug used in the permission name:

| Policy class | Derived slug | Example permission checked |
|---|---|---|
| `PostPolicy` | `Post` | `post.create` |
| `ServiceVisitPolicy` | `ServiceVisit` | `service_visit.view_any` |
| `CustomerOrderPolicy` | `CustomerOrder` | `customer_order.update` |

The derivation uses `Str::replace('Policy', '', class_basename(static::class))` — no namespace is involved.

---

## Full Policy Example

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Oltrematica\RoleLite\Trait\ChecksPermissions;

class PostPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->checkPermission($user, 'view_any');
    }

    public function view(User $user, Post $post): bool
    {
        return $this->checkPermission($user, 'view');
    }

    public function create(User $user): bool
    {
        return $this->checkPermission($user, 'create');
    }

    public function update(User $user, Post $post): bool
    {
        return $this->checkPermission($user, 'update');
    }

    public function delete(User $user, Post $post): bool
    {
        return $this->checkPermission($user, 'delete');
    }

    public function restore(User $user, Post $post): bool
    {
        return $this->checkPermission($user, 'restore');
    }

    public function forceDelete(User $user, Post $post): bool
    {
        return $this->checkPermission($user, 'force_delete');
    }
}
```

Each call to `checkPermission($user, 'create')` internally resolves the permission name as `post.create` and delegates to `PermissionService::userHasPermission`, which uses the two-tier cache.

---

## Custom Model Class Override

If the auto-derived model name does not match your permission naming — for example, if you have a policy that guards a differently-named model — pass the model class as the third argument:

```php
class ArticlePolicy
{
    use ChecksPermissions;

    public function create(User $user): bool
    {
        // Override: use 'blog_post' slug instead of 'article'
        return $this->checkPermission($user, 'create', 'BlogPost');
    }
}
```

The third parameter is a string passed directly to `Permission::buildPermissionName`, so it should be the **model class basename** (not a FQCN or slug).

---

## Getting the Permission Name

The trait also exposes `getPermissionName` for cases where you need the resolved name — for example, in test assertions:

```php
// Inside or outside the policy:
$policy = new PostPolicy();
$name = $policy->getPermissionName('create');
// Returns: "post.create"

$name = $policy->getPermissionName('create', 'Article');
// Returns: "article.create"
```

---

## Registering Policies

Register your policies as normal in `App\Providers\AuthServiceProvider`:

```php
protected $policies = [
    Post::class => PostPolicy::class,
];
```

Or use automatic policy discovery (Laravel 9+) — no changes needed.

---

## Using Policies in Controllers

Once registered, policies integrate seamlessly with standard Laravel authorization:

```php
// Controller
public function store(Request $request): Response
{
    $this->authorize('create', Post::class);
    // ...
}

// Blade
@can('create', App\Models\Post::class)
    <a href="{{ route('posts.create') }}">New Post</a>
@endcan
```

---

## Next Steps

- [Configuration Reference](configuration) — customise default actions and table names

---

[Home](index) | [Roles](roles) | [Permissions](permissions) | **Policies** | [Configuration](configuration)
