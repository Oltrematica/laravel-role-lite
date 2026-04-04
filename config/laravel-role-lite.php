<?php

declare(strict_types=1);

return [

    'table_names' => [
        /*
        |--------------------------------------------------------------------------
        | Role table Name
        |--------------------------------------------------------------------------
        |
        | This is the name of the table that will be used to store the roles.
        | You can change it to whatever you like.
        |
        */
        'roles' => 'roles',

        /*
        |--------------------------------------------------------------------------
        | Model table Name
        |--------------------------------------------------------------------------
        |
        | This is the name of the table that will be used to store the roles.
        | You can change it to whatever you like.
        |
        | Usually this is the name of the table that stores the users: 'users'
        */
        'users' => 'users',

        /*
        |--------------------------------------------------------------------------
        | RoleModel pivot Table Name
        |--------------------------------------------------------------------------
        |
        | This is the name of the table that will be used to store the roles.
        | You can change it to whatever you like.
        |
        */
        'role_user' => 'role_user',

        /*
        |--------------------------------------------------------------------------
        | Permissions Table Name
        |--------------------------------------------------------------------------
        |
        | This is the name of the table that will be used to store the permissions.
        | You can change it to whatever you like.
        |
        */
        'permissions' => 'permissions',

        /*
        |--------------------------------------------------------------------------
        | RolePermission pivot Table Name
        |--------------------------------------------------------------------------
        |
        | This is the name of the pivot table that will be used to store the
        | relationship between roles and permissions.
        | You can change it to whatever you like.
        |
        */
        'role_permission' => 'role_permission',
    ],

    /*
     |--------------------------------------------------------------------------
     | Permissions Settings
     |--------------------------------------------------------------------------
     |
     | Here you can configure the permissions system behaviour.
    */
    'permissions' => [
        /*
        |--------------------------------------------------------------------------
        | Cache TTL
        |--------------------------------------------------------------------------
        |
        | The time-to-live in seconds for the permissions cache.
        | Set to 0 to disable caching.
        |
        */
        'cache_ttl' => 3600,

        /*
        |--------------------------------------------------------------------------
        | Cache Prefix
        |--------------------------------------------------------------------------
        |
        | The prefix used for all permissions cache keys.
        |
        */
        'cache_prefix' => 'role_lite',

        /*
        |--------------------------------------------------------------------------
        | Default Actions
        |--------------------------------------------------------------------------
        |
        | The default set of CRUD actions available for permissions.
        | These follow Laravel policy naming conventions.
        |
        */
        'default_actions' => [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force_delete',
            'delete_any',
            'force_delete_any',
            'restore_any',
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Model Names
     |--------------------------------------------------------------------------
     |
     | Here you can specify the model names
    */
    'model_names' => [
        /*
        |--------------------------------------------------------------------------
        | User Model
        |--------------------------------------------------------------------------
        |
        | If you want to use a custom user model, you can specify it here.
        | Otherwise, the model will be automatically detected with
        | `config('auth.providers.users.model')`
        |
       */
        'user' => null,
    ],
];
