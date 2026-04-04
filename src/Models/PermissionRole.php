<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Oltrematica\RoleLite\Events\PermissionGranted;
use Oltrematica\RoleLite\Events\PermissionRevoked;
use Oltrematica\RoleLite\Services\ConfigService;

/**
 * @property int $id
 * @property int $role_id
 * @property int $permission_id
 */
class PermissionRole extends Pivot
{
    public $incrementing = true;

    /**
     * @var array<string, class-string>
     */
    protected $dispatchesEvents = [
        'created' => PermissionGranted::class,
        'deleted' => PermissionRevoked::class,
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(ConfigService::getRolePermissionTable());
    }
}
