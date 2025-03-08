<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Oltrematica\RoleLite\Models\RoleUser;

class UserRoleUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly RoleUser $roleUser)
    {
        Log::debug('UserRoleUpdated event fired', [
            'user_id' => $roleUser->user_id,
            'role_id' => $roleUser->role_id,
        ]);
    }
}
