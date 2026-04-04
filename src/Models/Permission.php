<?php

declare(strict_types=1);

namespace Oltrematica\RoleLite\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Oltrematica\RoleLite\Services\ConfigService;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 */
class Permission extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(ConfigService::getPermissionsTable());
    }

    public static function getModelSlug(string $modelClass): string
    {
        return Str::snake(class_basename($modelClass));
    }

    public static function buildPermissionName(string $modelClass, string $action): string
    {
        return self::getModelSlug($modelClass).'.'.$action;
    }

    public static function findOrCreateForModel(string $modelClass, string $action, ?string $description = null): self
    {
        $name = self::buildPermissionName($modelClass, $action);

        return self::firstOrCreate(
            ['name' => $name],
            ['description' => $description],
        );
    }

    /**
     * @param  list<string>|null  $actions
     * @return Collection<int, self>
     */
    public static function createForModel(string $modelClass, ?array $actions = null): Collection
    {
        $actions ??= ConfigService::getDefaultActions();

        $permissions = new Collection();
        foreach ($actions as $action) {
            $permissions->push(self::findOrCreateForModel($modelClass, $action));
        }

        return $permissions;
    }

    /**
     * @return BelongsToMany<Role, covariant $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            related: Role::class,
            table: ConfigService::getRolePermissionTable(),
            foreignPivotKey: 'permission_id',
            relatedPivotKey: 'role_id',
        )->using(PermissionRole::class);
    }
}
