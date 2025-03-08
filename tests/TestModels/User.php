<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Tests\TestModels;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Oltrematica\RoleLite\Trait\HasRoles;

class User extends Authenticatable
{
    use HasRoles;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}
