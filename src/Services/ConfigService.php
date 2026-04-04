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

    public static function getPermissionsTable(): string
    {
        /** @var string $table */
        $table = config('oltrematica-role-lite.table_names.permissions', 'permissions');

        return $table;
    }

    public static function getRolePermissionTable(): string
    {
        /** @var string $table */
        $table = config('oltrematica-role-lite.table_names.role_permission', 'role_permission');

        return $table;
    }

    public static function getCacheTtl(): int
    {
        /** @var int $ttl */
        $ttl = config('oltrematica-role-lite.permissions.cache_ttl', 3600);

        return $ttl;
    }

    public static function getCachePrefix(): string
    {
        /** @var string $prefix */
        $prefix = config('oltrematica-role-lite.permissions.cache_prefix', 'role_lite');

        return $prefix;
    }

    /**
     * @return list<string>
     */
    public static function getDefaultActions(): array
    {
        /** @var list<string> $actions */
        $actions = config('oltrematica-role-lite.permissions.default_actions', [
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
        ]);

        return $actions;
    }

    public static function getUserModel(): string
    {
        if (! config('oltrematica-role-lite.model_names.user')) {
            /** @var string $config */
            $config = config('auth.providers.users.model', 'App\Models\User');

            return $config;
        }

        /** @var string $model */
        $model = config('oltrematica-role-lite.model_names.user', 'App\Models\User');

        return $model;
    }
}
