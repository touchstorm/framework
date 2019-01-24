<?php

namespace Chronos\Kernel;

use Auryn\Injector;

class ScheduledKernel extends Kernel
{
    /**
     * @var Injector $app
     */
    protected $app;

    protected $controller;

    protected $method;

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
     * Handle the console command
     * @param $input
     * @param null $output
     * @return string
     */
    public function handle($input, $output = null)
    {
        $this->load($input);

        try {

            $controller = $this->register();

            $response = $this->dispatch($controller);

            if ($output) {
                return $this->output($response);
            }

        } catch (Exception $e) {
            return 'File: ' . $e->getFile() . ' | ' . $e->getLine() . ' | ' . $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * Load
     * Break down the argument vectors passed
     * through to the kernel and extract the
     * controller and method
     * @param array $input
     */
    protected function load(array $input)
    {
        $vectors = explode('@', $input[1]);
        $this->controller = $vectors[0];
        $this->method = $vectors[1];
    }

    /**
     * Register
     * Register any controller specific service providers
     */
    protected function register()
    {
        $controller = $this->app->make($this->namespace . $this->controller);

        // Register the controller's providers
        foreach ($controller->providers as $provider) {
            $this->app->execute([$provider, 'registrar']);
        }

        return $controller;
    }

    /**
     * Dispatch the controller command
     * @param $controller
     * @return mixed
     * @throws \Auryn\InjectionException
     */
    protected function dispatch($controller)
    {
        return $this->app->execute([$controller, $this->method]);
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
     * Console output
     * @return string
     */
    protected function details()
    {
        return $this->controller . '@' . $this->method . " |\tfinished in " . round(microtime(true) - $this->timestamp, 4) . " secs" . PHP_EOL;
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

    /**
     * @return string $controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string $method
     */
    public function getMethod()
    {
        return $this->method;
    }

}
