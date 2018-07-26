<?php

namespace Chronos\Providers;

use Auryn\Injector;

abstract class ServiceProvider
{
    /**
     * Application container
     * @var Injector $app
     */
    protected $app;

    /**
     * Loads the service providers
     * @param Injector $app
     * @return mixed
     */
    public function registrar(Injector $app)
    {
        $this->app = $app;

        // Call the specified method running | thread
        call_user_func([$this, 'register']);

        // Return the container
        return $this->app;
    }
}