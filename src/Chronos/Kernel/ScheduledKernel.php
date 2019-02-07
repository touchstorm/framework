<?php

namespace Chronos\Kernel;

use Chronos\Exceptions\KernelException;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Exception;

class ScheduledKernel extends Kernel
{
    /**
     * @var string $controller
     */
    protected $controller;

    /**
     * @var string $method
     */
    protected $method;
    /**
     * @var string
     */
    protected $controllersNamespace;

    public function __construct(Application $app, ArgumentVectors $arguments, $CONTROLLERS = '')
    {
        parent::__construct($app, $arguments);

        $this->controllersNamespace = $CONTROLLERS;
    }

    /**
     * Parse Console Arguments
     * Break down the argument vectors passed
     * through to the kernel and extract the
     * getController and getMethod
     */
    protected function parseConsoleArguments()
    {
        $args = $this->arguments->forScheduled();

        $this->controller = $args->getController();
        $this->method = $args->getMethod();
    }

    /**
     * Handle the console command
     * @param bool $output
     * @return string
     */
    public function handle($output = false)
    {
        try {

            $response = $this->dispatch();

            if ($output) {
                return $this->output($response);
            }

        } catch (Exception $e) {
            return 'File: ' . $e->getFile() . ' | ' . $e->getLine() . ' | ' . $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * Dispatch the getController command
     * @return mixed
     * @throws \Auryn\InjectionException
     */
    protected function dispatch()
    {
        return $this->app->resolveAndExecute([$this->controllersNamespace . $this->controller, $this->method]);
    }

    /**
     * Console output
     * @return string
     */
    protected function details()
    {
        return $this->controller . '@' . $this->method . " |\tfinished in " . round(microtime(true) - $this->timestamp, 4) . " secs" . PHP_EOL;
    }

    /**
     * @return string $getController
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string $getMethod
     */
    public function getMethod()
    {
        return $this->method;
    }

}
