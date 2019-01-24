<?php

namespace Chronos\Providers;

use Illuminate\Database\Capsule\Manager;

class EloquentServiceProvider extends ServiceProvider
{
    /**
     * Register Eloquent
     */
    public function register()
    {
        $capsule = new Manager;
        $connections = require_once getenv('APP_BASE') . '/config/connections.php';

        // Set connections
        foreach ($connections as $name => $connection) {
            $capsule->addConnection($connection, $name);
        }

        // Boot the Eloquent capsule
        $capsule->bootEloquent();
    }

}