<?php

namespace Chronos\Kernel;

use Chronos\Dispatchers\Threads;
use Chronos\Services\ThreadedService;
use Exception;

class RunningKernel extends Kernel
{
    /**
     * @var string $service
     */
    protected $service;

    /**
     * Parse Console Arguments
     * Break down the argument vectors passed
     * through to the kernel and extract the
     * controller and method
     */
    protected function parseConsoleArguments()
    {
        $this->service = $this->arguments->running()->service();
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

            // Create the service
            $service = $this->app->make($this->namespace . $this->service, [':app' => $this->app]);


            // Register the providers for the running service
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

}
