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
     * Method on getController
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
    public function forScheduled()
    {
        return $this->type('scheduled');
    }

    /**
     * Running argument vector type
     * @return ArgumentVectors
     * @throws ArgumentVectorException
     */
    public function forRunning()
    {
        return $this->type('running');
    }

    /**
     * Batch argument vector type
     * @return ArgumentVectors
     * @throws ArgumentVectorException
     */
    public function forBatch()
    {
        return $this->type('batch');
    }

    /**
     * Running thread argument vector type
     * @return ArgumentVectors
     * @throws ArgumentVectorException
     */
    public function forRunningThread()
    {
        return $this->type('runningThread');
    }

    /**
     * Batch thread argument vector type
     * @return ArgumentVectors
     * @throws ArgumentVectorException
     */
    public function forBatchThread()
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
            throw new ArgumentVectorException('No argument type passed.', 422);
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

        throw new ArgumentVectorException('No argument parser exists for this type', 422);
    }

    /**
     * Get getController
     * @return void
     */
    public function getController()
    {
        return $this->controller ?? $this->setController();
    }

    /**
     * Get getMethod
     * @return string
     */
    public function getMethod()
    {
        return $this->method ?? $this->setMethod();
    }

    /**
     * Get getController
     * @return void
     */
    public function getService()
    {
        return $this->service ?? $this->setService();
    }

    /**
     * Get Queue id
     * @return int|null|string
     */
    public function getQueueId()
    {
        return $this->queueId ?? $this->setQueueId();
    }

    /**
     * Get Batch Queue id
     * tilda separated string of ids
     * @return null|string
     */
    public function getBatchQueueId()
    {
        return $this->batchedQueueId ?? $this->setBatchedQueueId();
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
     * Set the getController
     * @return string
     */
    protected function setController()
    {
        return $this->controller;
    }

    /**
     * Set the getMethod
     * @return string
     */
    protected function setMethod()
    {
        return $this->method;
    }

    /**
     * Set the getController
     * @return string
     */
    protected function setQueueId()
    {
        return $this->queueId;
    }

    /**
     * Set the getController
     * @return string
     */
    protected function setBatchedQueueId()
    {
        return $this->batchedQueueId;
    }

    /**
     * Set the getMethod
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
        if (!strstr($this->file, 'scheduled.php')) {
            throw new ArgumentVectorException('Dispatch argument vector mismatch. (' . $this->file . ') not recognized.', 422);
        }

        $this->controller = $this->argumentExplode();
        $this->method = $this->argumentExplode(1);
    }

    /**
     * Parse arguments for running dispatch kernel
     */
    private function parseRunningArguments()
    {
        if (!strstr($this->file, 'running.php')) {
            throw new ArgumentVectorException('Dispatch argument vector mismatch. (' . $this->file . ') not recognized.', 422);
        }

        $this->service = $this->arguments[0];
    }

    /**
     * Parse arguments for running thread dispatch kernel
     */
    private function parseRunningThreadArguments()
    {
        if (!strstr($this->file, 'thread.php')) {
            throw new ArgumentVectorException('Dispatch argument vector mismatch. (' . $this->file . ') not recognized.', 422);
        }

        $this->queueId = $this->arguments[0];
        $this->service = $this->arguments[1];
    }

    /**
     * Parse arguments for batch dispatch kernel
     */
    private function parseBatchArguments()
    {
        if (!strstr($this->file, 'batch.php')) {
            throw new ArgumentVectorException('Dispatch argument vector mismatch. (' . $this->file . ') not recognized.', 422);
        }

        $this->service = $this->arguments[0];
    }

    /**
     * Parse arguments for batch thread dispatch kernel
     */
    private function parseBatchThreadArguments()
    {
        if (!strstr($this->file, 'batchThread.php')) {
            throw new ArgumentVectorException('Dispatch argument vector mismatch. (' . $this->file . ') not recognized.', 422);
        }

        $this->batchedQueueId = $this->arguments[0];
        $this->service = $this->arguments[1];
    }
}