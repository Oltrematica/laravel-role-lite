<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Oltrematica\RoleLite\Services\ConfigService;

class RoleUser extends Pivot
{
    public $incrementing = true;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(ConfigService::getRoleUserTable());
    }
}
