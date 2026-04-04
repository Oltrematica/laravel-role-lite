<?php

declare(strict_types=1);

use Oltrematica\RoleLite\Services\ConfigService;

describe('ConfigService::getPermissionsTable()', function (): void {
    test('returns the default permissions table name', function (): void {
        expect(ConfigService::getPermissionsTable())->toBe('permissions');
    });

    test('returns configured permissions table name when set', function (): void {
        config()->set('oltrematica-role-lite.table_names.permissions', 'custom_permissions');

        expect(ConfigService::getPermissionsTable())->toBe('custom_permissions');
    });
});

describe('ConfigService::getRolePermissionTable()', function (): void {
    test('returns the default role_permission table name', function (): void {
        expect(ConfigService::getRolePermissionTable())->toBe('role_permission');
    });

    test('returns configured role_permission table name when set', function (): void {
        config()->set('oltrematica-role-lite.table_names.role_permission', 'custom_role_permission');

        expect(ConfigService::getRolePermissionTable())->toBe('custom_role_permission');
    });
});

describe('ConfigService::getCacheTtl()', function (): void {
    test('returns the default cache TTL of 3600', function (): void {
        expect(ConfigService::getCacheTtl())->toBe(3600);
    });

    test('returns configured cache TTL when set', function (): void {
        config()->set('oltrematica-role-lite.permissions.cache_ttl', 7200);

        expect(ConfigService::getCacheTtl())->toBe(7200);
    });
});

describe('ConfigService::getCachePrefix()', function (): void {
    test('returns the default cache prefix', function (): void {
        expect(ConfigService::getCachePrefix())->toBe('role_lite');
    });

    test('returns configured cache prefix when set', function (): void {
        config()->set('oltrematica-role-lite.permissions.cache_prefix', 'my_prefix');

        expect(ConfigService::getCachePrefix())->toBe('my_prefix');
    });
});

describe('ConfigService::getDefaultActions()', function (): void {
    test('returns an array of 10 default CRUD actions', function (): void {
        $actions = ConfigService::getDefaultActions();

        expect($actions)->toBeArray()
            ->toHaveCount(10);
    });

    test('returns the expected default actions', function (): void {
        $actions = ConfigService::getDefaultActions();

        expect($actions)->toContain('view')
            ->toContain('view_any')
            ->toContain('create')
            ->toContain('update')
            ->toContain('delete')
            ->toContain('delete_any')
            ->toContain('restore')
            ->toContain('restore_any')
            ->toContain('force_delete')
            ->toContain('force_delete_any');
    });

    test('returns configured default actions when set', function (): void {
        config()->set('oltrematica-role-lite.permissions.default_actions', ['view', 'create', 'delete']);

        expect(ConfigService::getDefaultActions())->toBe(['view', 'create', 'delete']);
    });
});
