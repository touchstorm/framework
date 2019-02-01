<?php

use Chronos\Helpers\ArgumentVectors;

class MockServiceProvider extends \Chronos\Providers\ServiceProvider
{
    public function register()
    {
        $controller = 'MockController';
        $method = 'someMethod';
        $this->app->define(ArgumentVectors::class, [
            ':argv' => [
                'scheduled.php',
                $controller . '@' . $method
            ]
        ]);

        $this->app->defineParam('runFoo', 'bar');
        $this->app->defineParam('fooValue', 'bar');
    }
}