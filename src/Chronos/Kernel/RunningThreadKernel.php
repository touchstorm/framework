<?php

namespace Chronos\Kernel;

use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Exception;

class RunningThreadKernel extends Kernel
{
    /**
     * Queue item id
     * @var string $id
     */
    protected $id;

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
        $args = $this->arguments->forRunningThread();

        $this->id = $args->getQueueId();
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
             * @var \MockRunningService $service
             */
            $service = $this->app->make($this->controllersNamespace . '\\' . $this->getService(), [':app' => $this->app]);

            $service->register($this->getMethod(), $this->getid());

            $response = $this->app->execute(['Thread', 'handle']);

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
     * @return string $getController
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return string $getController
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string $getMethod
     */
    public function getMethod()
    {
        return $this->method;
    }

}
