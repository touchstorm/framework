<?php

namespace Chronos\Kernel;

use Chronos\Dispatchers\Threads;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Exception;

class RunningKernel extends Kernel
{
    /**
     * @var string $service
     */
    protected $service;

    /**
     * @var string $servicesNamespace
     */
    protected $servicesNamespace;

    public function __construct(Application $app, ArgumentVectors $arguments, $SERVICES = '')
    {
        parent::__construct($app, $arguments);

        $this->servicesNamespace = $SERVICES;
    }

    /**
     * Parse Console Arguments
     * Break down the argument vectors passed
     * through to the kernel and extract the
     * getController and getMethod
     */
    protected function parseConsoleArguments()
    {
        $this->service = $this->arguments->forRunning()->getService();
    }

    /**
     * Handle the console command
     * @param null $output
     * @param array $options
     * @return string
     */
    public function handle($output = null, $options = [])
    {
        try {

            // Create the getService
            $service = $this->app->make($this->namespace . $this->service, [':app' => $this->app]);

            // Register the providers for the running getService
            $this->app = $service->register('running');

            // Now that our dependencies are bound into the
            // IoC container, we can begin Dispatching threads
            // for this running task.
            $this->app->execute([Threads::class, 'handle'], [
                ':options' => [
                    'vectors' => [$this->service],
                    'settings' => $options
                ]
            ]);

        } catch (Exception $e) {
            return 'File: ' . $e->getFile() . ' | ' . $e->getLine() . ' | ' . $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set a namespace
     * @param string $namespace
     */
    public function setNamespace(string $namespace)
    {
        // TODO: Implement setNamespace() method.
    }

    /**
     * @return string $namespace
     */
    public function getNamespace()
    {
        // TODO: Implement getNamespace() method.
    }
}
