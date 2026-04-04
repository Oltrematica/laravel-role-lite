<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Oltrematica\RoleLite\Models\PermissionRole;

class PermissionGranted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly PermissionRole $permissionRole)
    {
        Log::debug('PermissionGranted event fired', [
            'role_id' => $permissionRole->role_id,
            'permission_id' => $permissionRole->permission_id,
        ]);
    }
}
