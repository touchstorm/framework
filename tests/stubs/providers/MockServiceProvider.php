<?php


class MockServiceProvider extends \Chronos\Providers\ServiceProvider
{
    public function register()
    {
        $this->app->defineParam('runFoo', 'bar');
        $this->app->defineParam('fooValue', 'bar');

        $this->app->defineParam('CONTROLLERS', '\\');
        $this->app->defineParam('THREADS', '\\');
        $this->app->defineParam('REPOSITORIES', '\\');
        $this->app->defineParam('SERVICES', '\\');
        $this->app->defineParam('PROVIDERS', '\\');
    }
}