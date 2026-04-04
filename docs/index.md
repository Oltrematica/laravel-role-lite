---
layout: default
title: Home
nav_order: 1
---

# Laravel Role Lite

**Lightweight role & permission management for Laravel**

[![Latest Version on Packagist](https://img.shields.io/packagist/v/oltrematica/laravel-role-lite.svg?style=flat-square)](https://packagist.org/packages/oltrematica/laravel-role-lite)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-blue?style=flat-square)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10%20%7C%2011%20%7C%2012%20%7C%2013-red?style=flat-square)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

---

## What is Laravel Role Lite?

Laravel Role Lite is a minimal, focused package that brings role-based access control (RBAC) and optional permission management to your Laravel application — without the overhead of heavier solutions.

- **Role management** — assign, remove, and check roles on any Eloquent model using backed PHP enums or plain strings
- **Permission management** *(opt-in)* — database-driven permissions tied to roles, with a two-tier cache for high performance
- **Policy integration** — `ChecksPermissions` trait wires your Laravel Policies directly to the permission system
- **Event-driven** — role and permission changes dispatch Laravel events you can listen to

```bash
composer require oltrematica/laravel-role-lite
```

---

## Quick Start

```php
// Assign a role and check it
$user->assignRole(UserRole::Admin);
$user->hasRole(UserRole::Admin); // true

// Grant a permission and check it
$user->givePermissionTo('post.create');
$user->hasPermissionTo('post.create'); // true

// Check via canDo helper
$user->canDo(Post::class, 'create'); // true
```

---

## Documentation

| Page | Description |
|------|-------------|
| [Roles](roles) | Installing, configuring, and working with roles |
| [Permissions](permissions) | Opt-in permission system — model permissions, cache, events |
| [Policy Integration](policies) | Using `ChecksPermissions` in Laravel Policies |
| [Configuration Reference](configuration) | All config keys explained |

---

## Requirements

- PHP 8.3+
- Laravel 10, 11, 12, or 13

---

## License

MIT — [Oltrematica](https://github.com/Oltrematica)
