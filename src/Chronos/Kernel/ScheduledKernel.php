<?php

namespace Chronos\Kernel;

use Chronos\Exceptions\KernelException;
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
     * Parse Console Arguments
     * Break down the argument vectors passed
     * through to the kernel and extract the
     * controller and method
     */
    protected function parseConsoleArguments()
    {
        $this->controller = $this->arguments->type('scheduled')->controller();
        $this->method = $this->arguments->type('scheduled')->method();
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
     * Dispatch the controller command
     * @return mixed
     * @throws \Auryn\InjectionException
     */
    protected function dispatch()
    {
        return $this->app->resolveAndExecute([$this->namespace . $this->controller, $this->method]);
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
