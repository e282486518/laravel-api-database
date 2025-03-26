<?php

return [

    /*
    |--------------------------------------------------------------------------
    | dcat-admin auth setting
    |--------------------------------------------------------------------------
    |
    | Authentication settings for all admin pages. Include an authentication
    | guard and a user provider setting of authentication driver.
    |
    | You can specify a controller for `login` `logout` and other auth routes.
    |
    */
    'auth' => [

        'providers' => [
            'admin' => [
                //'driver' => 'eloquent',
                //'model'  => Dcat\Admin\Models\Administrator::class,
                'driver' => 'api',
                'model'  => App\Models\AdministratorApi::class,
            ],
        ],

    ],

];
