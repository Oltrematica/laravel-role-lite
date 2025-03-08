<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Services;

readonly class ConfigService
{
    public static function getRolesTable(): string
    {
        /** @var string $table */
        $table = config('oltrematica-role-lite.table_names.roles', 'role_user');

        return $table;
    }

    public static function getRoleUserTable(): string
    {
        /** @var string $table */
        $table = config('oltrematica-role-lite.table_names.role_user', 'role_user');

        return $table;

    }

    public static function getUserTable(): string
    {
        /** @var string $table */
        $table = config('oltrematica-role-lite.table_names.users', 'users');

        return $table;
    }

    public static function getUserModel(): string
    {
        /** @var string $model */
        $model = config('oltrematica-role-lite.model_names.user', config('auth.providers.users.model', 'App\Models\User'));

        return $model;
    }
}
