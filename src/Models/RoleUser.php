<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoleUser extends Pivot
{
    public $incrementing = true;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('role-lite.role_user_table'));
    }
}
