<?php

namespace Chronos\Helpers;

use Chronos\Exceptions\ArgumentVectorException;

class ArgumentVectors
{
    /**
     * @var array
     */
    protected $consoleFileArgumentMap = [
        'scheduled' => 'parseScheduledArguments',
        'running' => 'parseRunningArguments',
        'runningThread' => 'parseRunningThreadArguments',
        'batch' => 'parseBatchArguments',
        'batchThread' => 'parseBatchThreadArguments'
    ];

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
     * Threaded services will use an integer
     * @var null|int $queueId
     */
    protected $queueId = null;

    /**
     * Batched Threads will use a delimiter separated
     * string of ids (1~2~3~4)
     * @var null | string
     */
    protected $batchedQueueId = null;

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

    /**
     * Cache of previously parsed arguments
     * @var array
     */
    protected $parsed = [];

    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->extractArguments($argv);
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
     * Scheduled argument vector type
     * @return ArgumentVectors
     * @throws ArgumentVectorException
     */
    public function scheduled()
    {
        return $this->type('scheduled');
    }

    /**
     * Running argument vector type
     * @return ArgumentVectors
     * @throws ArgumentVectorException
     */
    public function running()
    {
        return $this->type('running');
    }

    /**
     * Batch argument vector type
     * @return ArgumentVectors
     * @throws ArgumentVectorException
     */
    public function batch()
    {
        return $this->type('batch');
    }

    /**
     * Running thread argument vector type
     * @return ArgumentVectors
     * @throws ArgumentVectorException
     */
    public function runningThread()
    {
        return $this->type('runningThread');
    }

    /**
     * Batch thread argument vector type
     * @return ArgumentVectors
     * @throws ArgumentVectorException
     */
    public function batchThread()
    {
        return $this->type('batchThread');
    }

    /**
     * What type of file are we parsing
     * argument for?
     * @param string $type
     * @return $this
     * @throws ArgumentVectorException
     */
    public function type($type = '')
    {
        if (empty($type)) {
            throw new ArgumentVectorException('No argument type passed.', 500);
        }

        $this->mapArguments($type);

        return $this;
    }

    /**
     * Parse the arguments based on the
     * callback type
     * @param string $type
     * @throws ArgumentVectorException
     */
    protected function mapArguments(string $type)
    {
        foreach ($this->consoleFileArgumentMap as $t => $callback) {

            if (!method_exists($this, $callback)) {
                continue;
            }

            if ($t == $type) {

                // Already been parsed no need to remap
                if (in_array($type, $this->parsed)) {
                    return;
                }

                call_user_func([$this, $callback]);

                $this->parsed[] = $type;

                return;
            }
        }

        throw new ArgumentVectorException('No argument parser exists for this type', 500);
    }

    /**
     * Get controller
     * @return void
     */
    public function controller()
    {
        return $this->controller ?? $this->setController();
    }

    /**
     * Get method
     * @return string
     */
    public function method()
    {
        return $this->method ?? $this->setMethod();
    }

    /**
     * Get controller
     * @return void
     */
    public function service()
    {
        return $this->service ?? $this->setService();
    }

    public function queueId()
    {
        return $this->queueId ?? $this->setQueueId();
    }

    public function batchQueueId()
    {
        return $this->batchedQueueId ?? $this->setQueueId();
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
     * Get argument by index
     * @param int $index
     * @return array
     */
    public function getArgument($index = 0)
    {
        return $this->arguments[$index] ?? null;
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
        return $this->controller;
    }

    /**
     * Set the method
     * @return string
     */
    protected function setMethod()
    {
        return $this->method;
    }

    /**
     * Set the controller
     * @return string
     */
    protected function setQueueId()
    {
        return $this->queueId;
    }

    /**
     * Set the controller
     * @return string
     */
    protected function setBatchedQueueId()
    {
        return $this->queueId;
    }

    /**
     * Set the method
     * @return string
     */
    protected function setService()
    {
        return $this->method;
    }

    /**
     * Parse arguments for scheduled dispatch kernel
     */
    private function parseScheduledArguments()
    {
        if ($this->file !== 'scheduled.php') {
            throw new ArgumentVectorException('Dispatch argument vector mismatch', 500);
        }

        $this->controller = $this->argumentExplode();
        $this->method = $this->argumentExplode(1);
    }

    /**
     * Parse arguments for running dispatch kernel
     */
    private function parseRunningArguments()
    {
        if ($this->file !== 'running.php') {
            throw new ArgumentVectorException('Dispatch argument vector mismatch', 500);
        }

        $this->service = $this->arguments[0];
    }

    /**
     * Parse arguments for running thread dispatch kernel
     */
    private function parseRunningThreadArguments()
    {
        if ($this->file !== 'thread.php') {
            throw new ArgumentVectorException('Dispatch argument vector mismatch', 500);
        }

        $this->queueId = $this->arguments[0];
        $this->service = $this->arguments[1];
    }

    /**
     * Parse arguments for batch dispatch kernel
     */
    private function parseBatchArguments()
    {
        if ($this->file !== 'batch.php') {
            throw new ArgumentVectorException('Dispatch argument vector mismatch', 500);
        }

        $this->service = $this->arguments[0];
    }

    /**
     * Parse arguments for batch thread dispatch kernel
     */
    private function parseBatchThreadArguments()
    {
        if ($this->file !== 'batchThread.php') {
            throw new ArgumentVectorException('Dispatch argument vector mismatch', 500);
        }

        $this->batchedQueueId = $this->argumentExplode(0, '~');
        $this->service = $this->arguments[1];
    }
}