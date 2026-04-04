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
        Schema::create(RoleLiteConfigService::getPermissionsTable(), function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(RoleLiteConfigService::getPermissionsTable());
    }
};
