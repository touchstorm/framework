<?php

namespace Chronos\Kernel;

use Chronos\Dispatchers\Batches;
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
     * Parse Console Arguments
     * Break down the argument vectors passed
     * through to the kernel and extract the
     * controller and method
     */
    protected function parseConsoleArguments()
    {
        $this->service = $this->arguments
            ->batch()
            ->service();
    }

    /**
     * Handle the console command
     * @param array $options
     * @return string
     */
    public function handle($options = [])
    {
        try {

            // Create the service
            $service = $this->app->make($this->namespace . $this->service, [':app' => $this->app]);

            // Register the providers for the batch service
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
