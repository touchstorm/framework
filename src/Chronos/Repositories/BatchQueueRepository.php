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
class BatchQueueRepository
{
    /**
     * @var int $maxThreads
     * - Default value for maximum threads to run concurrently
     */
    protected $maxThreads = 1;

    /**
     * @var int $batchSize
     * - Default value is 0 which disables this feature
     */
    protected $batchSize = 0;

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
     * @var Model $queue
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

        // Configure Queue
        $this->queue->setConnection($this->connection);
        $this->queue->setTable($this->table);
        $this->batch = new Collection();
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
     * Items
     * - Retrieve a batch of items out of the queue
     * @param array $ids
     * @return Collection | null
     */
    public function items(Array $ids = [])
    {
        return $this->queue
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * Next
     * - Pop the next item out of the
     * batch for threading.
     * @return Queue
     */
    public function next()
    {
        if ($this->batchSize) {
            return $this->batch->chunk($this->batchSize)->first();
        }

        return $this->batch->pop();
    }

    /**
     * Fill
     * - Fill the batch container
     * using default query arguments.
     * @param $options
     * - Allow for Closure options
     * @return bool | Collection
     */
    public function fill($options = null)
    {
        // All you to pass a batch to the fill
        if ($options instanceof Collection) {
            $this->batch = $options;
            return;
        }

        // Set a limit. We'll fetch more queues
        // than are needed so we aren't making
        // excess requests to the database
        $limit = $this->batchSize * $this->maxThreads;

        // Fill the batch from the queue
        $batch = $this->queue
            ->where('in_use', 0)
            ->where(function ($query) {
                $query->where('available_at', '<', new DateTime('now'))
                    ->orWhereNull('available_at');
            })
            ->orderBy('available_at', 'DESC')
            ->limit($limit);

        // Resolve any closure options
        if ($options instanceof Closure) {
            $batch = $options($batch);
        }

        // Get & set batch
        $this->batch = $batch->get();

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
        // Guard
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

        // Update the batch's items to be in use
        $this->queue->whereIn('id', $ids)->update(['in_use' => 1]);
    }

    /**
     * reduce
     * @param array $ids
     * - Reduce the repositories batch by id array.
     */
    public function reduce($ids = [])
    {
        $this->batch = $this->batch->filter(function ($queue) use ($ids) {
            return !in_array($queue->id, $ids);
        });

    }

    /**
     * reset
     * - Reset the queue based on input
     * - Default resets any queue items in use
     * @param mixed $options
     * @param array $fields
     */
    public function reset($options = null, $fields = [])
    {
        /**
         * @var Queue $queue
         */
        $queue = $this->queue;

        // Default reset
        // Deactivate any activated queue items
        if (!$options) {
            $queue = $queue->where('in_use', 1);
        }

        // Int assumes you are toggling
        // on or off the queue items
        if (is_int($options)) {
            $queue = $queue->where('in_use', $options);
        }

        // If a string is used '*'
        // We'll assume they want both
        if (is_string($options)) {
            $queue = $queue->whereIn('in_use', [0, 1]);
        }

        // If options are a closure
        // process the query
        if ($options instanceof Closure) {
            $queue = $options($queue);
        }

        // Merge any extra field updates
        // with default field update
        $params = array_merge(['in_use' => 0], $fields);

        // Run the query on the queue item
        $queue->update($params);
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