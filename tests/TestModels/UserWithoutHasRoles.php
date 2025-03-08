<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Tests\TestModels;

use Illuminate\Foundation\Auth\User as Authenticatable;

class UserWithoutHasRoles extends Authenticatable
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
