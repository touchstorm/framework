<?php

namespace Chronos\Kernel;

use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;

abstract class Kernel
{
    /**
     * @var Application $app
     */
    protected $app;

    /**
     * @var string $timestamp
     */
    public $timestamp;

    /**
     * @var ArgumentVectors $arguments
     */
    protected $arguments;

    /**
     * ScheduledKernel constructor.
     * @param Application $app
     * @param ArgumentVectors $arguments
     */
    public function __construct(Application $app, ArgumentVectors $arguments)
    {
        $this->timestamp = microtime(true);
        $this->app = $app;
        $this->arguments = $arguments;

        // Defined on the extended Kernels
        $this->parseConsoleArguments();
    }

    /**
     * @return void
     */
    abstract protected function parseConsoleArguments();

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
     * @return mixed handle
     */
    abstract public function handle();

    /**
     * @return Application
     */
    public function getContainer()
    {
        return $this->app;
    }

}