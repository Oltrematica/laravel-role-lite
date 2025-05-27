![GitHub Tests Action Status](https://github.com/Oltrematica/laravel-role-lite/actions/workflows/run-tests.yml/badge.svg)
![GitHub PhpStan Action Status](https://github.com/Oltrematica/laravel-role-lite/actions/workflows/phpstan.yml/badge.svg)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/oltrematica/laravel-role-lite.svg?style=flat-square)](https://packagist.org/packages/oltrematica/laravel-role-lite)
[![Total Downloads](https://img.shields.io/packagist/dt/oltrematica/laravel-role-lite.svg?style=flat-square)](https://packagist.org/packages/oltrematica/laravel-role-lite)


# Laravel Role Lite

A lightweight role management package for Laravel applications.

Laravel Role Lite is a lightweight package for managing user roles in Laravel applications. It provides
a simple and intuitive API for defining roles, assigning them to users throughout your application with minimal
configuration.

## Prerequisites

- Laravel v10, v11 and v12
- PHP 8.3 or higher

## Installation

```bash
composer require oltrematica/laravel-role-lite
```

After installing the package, publish migrations:

```bash
php artisan vendor:publish --tag=oltrematica-role-lite-migrations
```

Run the migrations to create the necessary database tables:

```bash
php artisan migrate
```

## Configuration

The package comes with a default configuration file that you can modify according to your needs. The configuration file
is located at `config/oltrematica-role-lite.php`. Maybe you can be satisfied with the default configuration, but if you
want to change it, you can publish the configuration file using the following command:

```bash
php artisan vendor:publish --tag=oltrematica-role-lite-config
```

### Table Names
You can customize the table names used by the package:

```php
'table_names' => [
    // Table for storing roles
    'roles' => 'roles',
    
    // Your users table (usually 'users')
    'users' => 'users',
    
    // Pivot table for role-user relationship
    'role_user' => 'role_user',
],
```

### Model Names
You can specify a custom User model:

```php
'model_names' => [
    // If you want to use a custom user model, specify it here
    // Otherwise, it will use the model defined in auth.providers.users.model
    'user' => null,
],
```

## Usage

I suggest you to use Enum for roles, but you can use string too.

```php

enum Roles: string
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case MODERATOR = 'moderator';
}

```

### Assigning Roles

```php
// Assign a role to a user
$user->assignRole('admin');

// or you can use Enum 
$user->assignRole(\App\Enums\Roles::ADMIN);
```

### Assign multiple roles

```php
$user->assignRoles(['editor', 'moderator']);
```

### Checking Roles

Check if a user has a specific role:

```php
if ($user->hasRole('admin')) {
    // User has admin role
}

// or

if ($user->hasRole(\App\Enums\Roles::ADMIN)) {
    // User has admin role
}
```

Check if a user has any of the given roles

```php
if ($user->hasAnyRole(['admin', 'editor'])) {
    // User has either admin or editor role
}

// or

if ($user->hasAnyRole([Roles::ADMIN, Roles::EDITOR])) {
    // User has either admin or editor role
}
```

Check if a user has all the given roles

```php

if ($user->hasAllRoles(['admin', 'editor'])) {
    // User has both admin and editor roles
}

// or

if ($user->hasAllRoles([Roles::ADMIN, Roles::EDITOR])) {
    // User has both admin and editor roles
}
```

Check if a user has no roles

```php
if ($user->hasNoRoles()) {
    // User has no roles
}
```

Check if a user has at least one role

```php
if ($user->hasSomeRoles()) {
    // User has at least one role
}
```

## Events

The package fires events when roles are assigned or removed from users. You can listen to these events in your
application to perform additional actions.

- `UserRoleCreated`: Fired when a role is assigned to a user.
- `UserRoleDeleted`: Fired when a role is removed from a user.
- `UserRoleUpdated`: Fired when a role is updated for a user.


## Code Quality

The project includes automated tests and tools for code quality control.

### Rector

Rector is a tool for automating code refactoring and migrations. It can be run using the following command:

```shell
composer refactor
```

### PhpStan

PhpStan is a tool for static analysis of PHP code. It can be run using the following command:

```shell
composer analyse
```

### Pint

Pint is a tool for formatting PHP code. It can be run using the following command:

```shell
composer format
```

### Automated Tests

The project includes automated tests and tools for code quality control.

```shell
composer test
```

## Contributing

Feel free to contribute to this package by submitting issues or pull requests. We welcome any improvements or bug fixes
you may have.

