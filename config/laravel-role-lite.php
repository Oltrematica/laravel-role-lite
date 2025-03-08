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
