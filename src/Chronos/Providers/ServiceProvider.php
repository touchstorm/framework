<?php

namespace Chronos\Providers;

use Chronos\Foundation\Application;

class ServiceProvider
{
    /**
     * Application container
     * @var Application $app
     */
    protected $app;

    /**
     * Loads the getService providers
     * @param Application $app
     * @return mixed
     */
    public function registrar(Application $app)
    {
        $this->app = $app;

        // Call the specified getMethod running | thread
        call_user_func([$this, 'register']);

        // Return the container
        return $this->app;
    }
}