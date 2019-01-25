<?php

namespace Chronos\Providers;

use Auryn\Injector;
use Chronos\Foundation\Application;

class ServiceProvider
{
    /**
     * Application container
     * @var Injector $app
     */
    protected $app;

    /**
     * Loads the service providers
     * @param Application $app
     * @return mixed
     */
    public function registrar(Application $app)
    {
        $this->app = $app;

        // Call the specified method running | thread
        call_user_func([$this, 'register']);

        // Return the container
        return $this->app;
    }
}