<?php

namespace Chronos\Providers;

use Illuminate\Database\Capsule\Manager;

class EloquentServiceProvider extends ServiceProvider
{
    /**
     * Eloquent getService provider
     */
    public function register()
    {
        $capsule = new Manager;
        $connections = require_once $this->app->configPath() . '/connections.php';

        // Set connections
        foreach ($connections as $name => $connection) {
            $capsule->addConnection($connection, $name);
        }

        // Boot the Eloquent capsule
        $capsule->bootEloquent();
    }

}