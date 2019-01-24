<?php

namespace Chronos\Providers;

use Chronos\Helpers\ArgumentVectors;

class ArgumentVectorServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->share(new ArgumentVectors($_SERVER['argv']));
    }
}