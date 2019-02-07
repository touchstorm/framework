<?php

use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\Kernel;

class MockKernel extends Kernel
{

    protected $namespaces = [];

    protected $namespace = [];

    public function __construct(Application $app, ArgumentVectors $arguments, $CONTROLLERS, $SERVICES, $THREADS, $REPOSITORIES, $PROVIDERS)
    {
        parent::__construct($app, $arguments);

        $this->namespace['controllers'] = $CONTROLLERS;
        $this->namespace['services'] = $SERVICES;
        $this->namespace['threads'] = $THREADS;
        $this->namespace['repositories'] = $REPOSITORIES;
        $this->namespace['providers'] = $PROVIDERS;
    }

    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespace;
    }


    /**
     * @return void
     */
    protected function parseConsoleArguments()
    {

    }

    /**
     * @return mixed handle
     */
    public function handle()
    {
        // TODO: Implement handle() method.
    }
}