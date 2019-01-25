<?php

namespace Chronos\Kernel;

use Chronos\Controllers\Controller;
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
     * Handle the console command
     * @param $input
     * @param null $output
     * @return string
     * @throws \Auryn\InjectionException
     * @throws KernelException
     */
    public function handle($input, $output = null)
    {
        $this->parseInput($input);

        $controller = $this->resolveController();

        try {

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
     * @throws KernelException
     */
    protected function parseInput(array $input)
    {
        $vectors = $this->extractArgumentVectors($input); // TODO resolve this from IoC

        if (!count($vectors) || count($vectors) < 2) {
            throw new KernelException('Scheduled service arguments are ill formed', 422);
        }

        $this->controller = $vectors[0];
        $this->method = $vectors[1];
    }

    protected function extractArgumentVectors(array $input)
    {
        return explode('@', $input[1]);
    }

    /**
     * Register
     * Register any controller specific service providers
     * @return Controller
     * @throws \Auryn\InjectionException
     */
    protected function resolveController()
    {
        $controller = $this->app->make($this->namespace . $this->controller);

        // Register the controller's providers
        foreach ($controller->providers as $provider) {
            // $this->app->register($provider);)
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
