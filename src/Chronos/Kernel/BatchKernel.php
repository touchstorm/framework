<?php

namespace Chronos\Kernel;

use Chronos\Dispatchers\Batches;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Exception;

class BatchKernel extends Kernel
{
    /**
     * @var string $service
     */
    protected $service;

    /**
     * @var null | Batches
     */
    protected $threader = null;

    /**
     * @var string
     */
    protected $servicesNamespace;

    /**
     * BatchKernel constructor
     * @param Application $app
     * @param ArgumentVectors $arguments
     * @param string $SERVICES resolved from IoC
     */
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
        $this->service = $this->arguments
            ->forBatch()
            ->getService();
    }

    /**
     * Handle the console command
     * @param array $options
     * @return string
     */
    public function handle($options = [])
    {
        try {

            // Create the getService
            $service = $this->app->make($this->serviceNamespace . '\\' . $this->service, [':app' => $this->app]);

            // Register the providers for the batch getService
            $this->app = $service->register('batch');

            // Now that our dependencies are bound into the
            // IoC container, we can begin Dispatching threads
            // for this running task.
            $this->app->execute([Batches::class, 'handle'], [
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
}
