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
     * ServiceProvider constructor.
     * @param Injector $app
     */
    public function __construct(Injector $app)
    {
        $this->app = $app;
    }

    /**
     * Loads the service providers
     * @return mixed
     */
    public function registrar()
    {
        // Call the specified method running | thread
        call_user_func([$this, 'register']);

        // Return the container
        return $this->app;
    }
}