<?php

namespace Chronos\Kernel;

use Chronos\Dispatchers\Threads;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Helpers\NamespaceManager;
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

    public function __construct(Application $app, ArgumentVectors $arguments, NamespaceManager $namespace)
    {
        parent::__construct($app, $arguments);

        $this->servicesNamespace = $namespace->getServiceNamespace();
    }

    /**
     * Parse Console Arguments
     * Break down the argument vectors passed
     * through to the kernel and extract the
     * service.
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

            // Create the service
            $service = $this->app->make($this->servicesNamespace . '\\' . $this->service, [':app' => $this->app]);

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
