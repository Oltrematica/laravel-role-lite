<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Oltrematica\RoleLite\Services\ConfigService as RoleLiteConfigService;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(RoleLiteConfigService::getRoleUserTable(), function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')->constrained(
                RoleLiteConfigService::getRolesTable()
            )->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained(
                RoleLiteConfigService::getUserTable()
            )->cascadeOnDelete()->cascadeOnUpdate();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(RoleLiteConfigService::getRoleUserTable());
    }
};
