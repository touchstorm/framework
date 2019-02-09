<?php

namespace Chronos\Repositories;

use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\Queue;
use Closure;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class QueueRepository
 * @package Chronos\Repositories
 */
class QueueRepository
{
    /**
     * @var int $maxThreads
     * - Default value for maximum threads to run concurrently
     */
    protected $maxThreads = 1;

    /**
     * Pause between threads
     * in microseconds.
     * Usage cases:
     * - Swamping the processors when multiple threads are running
     * - Burning through quota per sec caps on threaded API calls
     * @var int
     */
    protected $pause = 0;

    /**
     * @var Queue | QueueContract $queue
     * - The queue is an instance of Laravel's Model class
     */
    public $queue;

    /**
     * @var Collection $batch
     * - The batch is contained in a Laravel collection
     */
    public $batch;

    /**
     * @var string $logFile
     * - Default log files
     */
    protected $logFile = 'base.log';

    /**
     * QueueRepository constructor.
     * @param QueueContract $queue
     */
    public function __construct(QueueContract $queue)
    {
        $this->queue = $queue;

        // Configure the queue
        $this->configureQueue();
    }

    /**
     * TODO 1. Refactor this, repositories shouldn't be coupled to any one type of Queue nor should it have exceptions internally to handle them.
     * Configure any queue connections
     */
    protected function configureQueue()
    {
        if (!$this->queue instanceof Model) {
            return;
        }

        // Configure Queue
        $this->queue->setConnection($this->connection);
        $this->queue->setTable($this->table);
    }

    /**
     * Item
     * - Retrieve the Queue Item
     * - Queue items are the heart of each
     * thread. They supply the thread with all
     * the needed information to create the dependencies.
     * @param $id
     * @return Queue
     */
    public function item($id)
    {
        return $this->queue->find($id);
    }

    /**
     * Next
     * - Shift the next item out of the
     * batch to be dispatched in a thread.
     * @return Queue
     */
    public function next()
    {
        return $this->batch->shift();
    }

    /**
     * Fill
     * - Fill the batch container
     * using default query arguments.
     * @param $options
     * - Allow for Closure options
     * @return bool
     */
    public function fill($options = null)
    {
        // Allows you to pass a batch to the fill
        if ($options instanceof Collection) {
            $this->batch = $options;
            return;
        }

        // Set a limit. We'll fetch more queues
        // than are needed so we aren't making
        // excess requests to the database
        $limit = $this->maxThreads * 4;

        // Fetch a batch off the Queue
        $this->batch = $this->queue->fetch($limit, $options);

        // Set batch of rows in use
        $this->setBatchInUse();

        return !$this->batch->isEmpty();
    }

    /**
     * setBatchInUse
     * - Send a query to set each Queue
     * to set it in use.
     */
    public function setBatchInUse()
    {
        // Make sure that the batch has been set
        if (!$this->batch instanceof Collection) {
            return;
        }

        // Nothing to set in use, return
        if ($this->batch->isEmpty()) {
            return;
        }

        // Get ids of batch queue items
        $ids = $this->batch->pluck('id')->toArray();

        // Have the queue set these in use
        $this->queue->setInUse($ids);
    }

    /**
     * Reset
     * - Reset the queue based on input
     * - Default resets any queue items in use
     * @param mixed $options
     * @param array $fields
     */
    public function reset($options = null, $fields = [])
    {
        $this->queue->reset($options, $fields);
    }

    /**
     * isSleeping
     * - If the batch is not a collection, sleep.
     * - If is a collection and empty, sleep.
     * @return bool
     */
    public function isSleeping()
    {
        // If batch is not a collection then there was
        // nothing available on dispatch initialization.
        if (!$this->batch instanceOf Collection) {
            return true;
        }

        // If we're empty, then sleep the threader
        // there is nothing to dispatch.
        return $this->batch->isEmpty();
    }

    /**
     * setMaxThreads
     * - Setter for max threads to run
     * @param int $maxThreads
     */
    public function setMaxThreads($maxThreads = 1)
    {
        $this->maxThreads = $maxThreads;
    }

    /**
     * getLogFile
     * - Retrieve the log file set
     * on the repository.
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * getMaxThreads
     * @return int
     */
    public function getMaxThreads()
    {
        return $this->maxThreads;
    }

    /**
     * getBatchSize
     * @return int
     */
    public function getBatchSize()
    {
        return $this->batchSize;
    }

    /**
     * Get pause time
     * @return int
     */
    public function getPause()
    {
        return $this->pause;
    }
}