<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Models;

use Illuminate\Database\Eloquent\Model;
use Oltrematica\RoleLite\Services\ConfigService;

class Role extends Model
{
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(ConfigService::getRolesTable());
    }

    public function users()
    {
        return $this->belongsToMany(
            ConfigService::getUserModel(),
            ConfigService::getRoleUserTable(),
            'role_id',
            'user_id'
        );
    }

    protected $fillable = [
        'name'
    ];


}
