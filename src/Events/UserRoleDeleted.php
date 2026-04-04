<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Oltrematica\RoleLite\Models\RoleUser;

class UserRoleDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly RoleUser $roleUser) {}
}
