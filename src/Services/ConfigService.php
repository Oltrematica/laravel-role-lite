<?php

namespace Oltrematica\RoleLite\Services;

readonly class ConfigService
{
    public static function getRolesTable(): string
    {
        return config('oltrematica-role-lite.table_names.roles', 'roles');
    }

    public static function getRoleUserTable(): string
    {
        return config('oltrematica-role-lite.table_names.role_user', 'role_user');
    }

    public static function getUserTable(): string
    {
        return config('oltrematica-role-lite.table_names.users', 'users');
    }



}