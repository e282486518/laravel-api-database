<?php

namespace e282486518\LaravelApiDatabase;

use Illuminate\Database\Connection as ConnectionBase;
use Illuminate\Support\ServiceProvider as ServiceProviderBase;

class ApiServiceProvider extends ServiceProviderBase
{
    public function register()
    {
        ConnectionBase::resolverFor('api', static function ($connection, $database, $prefix, $config) {
            if (app()->has(Connection::class)) {
                return app(Connection::class);
            }

            return new Connection($connection, $database, $prefix, $config);
        });
    }
}
