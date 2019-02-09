<?php

namespace Chronos\Kernel;

use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Helpers\NamespaceManager;
use Exception;

class BatchThreadKernel extends Kernel
{
    /**
     * tilda (~) separated list of queue ids
     * @var string $id
     */
    protected $ids;

    /**
     * @var string $service
     */
    protected $service;

    /**
     * All batch threads are triggered off the
     * thread method
     * @var string $method
     */
    protected $method = 'thread';

    /**
     * @var string $controllerNamespace
     */
    protected $controllersNamespace;

    /**
     * BatchThreadKernel constructor.
     * @param Application $app
     * @param ArgumentVectors $arguments
     * @param NamespaceManager $namespace
     */
    public function __construct(Application $app, ArgumentVectors $arguments, NamespaceManager $namespace)
    {
        parent::__construct($app, $arguments);

        $this->controllersNamespace = $namespace->getControllerNamespace();
    }

    /**
     * Parse Console Arguments
     * Break down the argument vectors passed
     * through to the kernel and extract the
     * queueIds and service
     */
    protected function parseConsoleArguments()
    {
        $args = $this->arguments->forBatchThread();

        $this->ids = $args->getBatchQueueId();
        $this->service = $args->getService();
    }

    /**
     * Handle the console command
     * @param bool $output
     * @param array $options
     * @return string
     */
    public function handle($output = false, array $options = [])
    {
        try {
            /**
             * @var \MockBatchService $service
             */
            $service = $this->app->make($this->controllersNamespace . '\\' . $this->getService(), [':app' => $this->app]);

            $service->register($this->getMethod(), $this->getIds());

            $response = $this->app->execute(['Batch', 'handle']);

            if ($output) {
                echo $response;
            }

        } catch (Exception $e) {
            die('File: ' . $e->getFile() . ' | ' . $e->getLine() . ' | ' . $e->getMessage() . PHP_EOL);
        }
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
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return string
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
