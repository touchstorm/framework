<?php

namespace Chronos\Kernel;

use Auryn\Injector;

class Kernel
{
    /**
     * @var Injector $app
     */
    protected $app;

    public $timestamp;

    protected $namespace = "\\App\\Console\\Controllers\\";

    /**
     * ScheduledKernel constructor.
     * @param Injector $app
     */
    public function __construct(Injector $app)
    {
        $this->timestamp = microtime(true);
        $this->app = $app;
    }

    /**
     * Console output
     * @param string $msg
     * @return string
     */
    protected function output(string $msg)
    {
        return $msg;
    }

    /**
     * Set a namespace
     * @param string $namespace
     */
    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return string $namespace
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return Injector
     */
    public function getContainer()
    {
        return $this->app;
    }

}