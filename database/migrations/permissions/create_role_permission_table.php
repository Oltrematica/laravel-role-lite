<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Oltrematica\RoleLite\Services\ConfigService as RoleLiteConfigService;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(RoleLiteConfigService::getRolePermissionTable(), function (Blueprint $table): void {
            $table->id();

            $table->foreignId('role_id')->constrained(
                RoleLiteConfigService::getRolesTable()
            )->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permission_id')->constrained(
                RoleLiteConfigService::getPermissionsTable()
            )->cascadeOnDelete()->cascadeOnUpdate();

            $table->timestamps();

            $table->unique(['role_id', 'permission_id'], 'unique_role_permission');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(RoleLiteConfigService::getRolePermissionTable());
    }
};
