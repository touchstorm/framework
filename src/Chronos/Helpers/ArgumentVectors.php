<?php

namespace Chronos\Helpers;

class ArgumentVectors
{
    /**
     * @var array $argv
     */
    protected $argv = [];

    /**
     * File receiving the call
     * @var string $file
     */
    protected $file = '';

    /**
     * Arguments detected
     * @var array $arguments
     */
    protected $arguments = [];

    /**
     * Service for running and batches
     * @var null|string $service
     */
    protected $service = null;

    /**
     * Controller for scheduled tasks
     * @var null|string
     */
    protected $controller = null;

    /**
     * Method on controller
     * @var null
     */
    protected $method = null;

    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->extractArguments($argv);
    }

    /**
     * Get controller
     * @return void
     */
    public function getController()
    {
        return $this->controller ?? $this->setController();
    }

    /**
     * Get method
     * @return string
     */
    public function getMethod()
    {
        return $this->method ?? $this->setMethod();
    }

    /**
     * Get controller
     * @return void
     */
    public function getService()
    {
        return $this->service ?? $this->setService();
    }

    /**
     * Get arguments
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Extract arguments from argv
     * @param array $argv
     */
    protected function extractArguments(array $argv)
    {
        $this->file = array_shift($argv);
        $this->arguments = $argv;
    }

    /**
     * Search the arguments and explode
     * strings on symbol
     * @param string $symbol
     * @param int $index
     * @return string
     */
    protected function argumentExplode($index = 0, $symbol = '@'): string
    {
        foreach ($this->arguments as $argument) {

            $vectors = explode($symbol, $argument);

            if (count($vectors) > 1) {
                return $vectors[$index];
            }
        }

        return '';
    }

    /**
     * Set the controller
     * @return string
     */
    protected function setController()
    {
        return $this->controller = $this->argumentExplode();
    }

    /**
     * Set the method
     * @return string
     */
    protected function setMethod()
    {
        return $this->method = $this->argumentExplode(1);
    }
}