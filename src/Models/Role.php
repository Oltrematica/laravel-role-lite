<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Oltrematica\RoleLite\Services\ConfigService;

class Role extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(ConfigService::getRolesTable());
    }

    /**
     * @return BelongsToMany<Model, covariant $this>
     */
    public function users(): BelongsToMany
    {
        /** @var class-string<Model> $relatedModel */
        $relatedModel = ConfigService::getUserModel();

        return $this->belongsToMany(
            related: $relatedModel,
            table: ConfigService::getRoleUserTable(),
            foreignPivotKey: 'role_id',
            relatedPivotKey: 'user_id')->using(RoleUser::class);
    }
}
