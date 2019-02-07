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
     * @param mixed $msg
     * @return string
     */
    protected function output($msg)
    {
        //TODO use the console tables package to format a kick ass resposne
        //TODO inject that class into the construct
        if (is_array($msg)) {
            return print_r($msg, true);
        }

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