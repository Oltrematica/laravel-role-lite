<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Oltrematica\RoleLite\Events\UserRoleCreated;
use Oltrematica\RoleLite\Events\UserRoleDeleted;
use Oltrematica\RoleLite\Events\UserRoleUpdated;
use Oltrematica\RoleLite\Services\ConfigService;

/**
 * @property int $id
 * @property int $user_id
 * @property int $role_id
 */
class RoleUser extends Pivot
{
    public $incrementing = true;

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => UserRoleCreated::class,
        'updated' => UserRoleUpdated::class,
        'deleted' => UserRoleDeleted::class,
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(ConfigService::getRoleUserTable());
    }
}
