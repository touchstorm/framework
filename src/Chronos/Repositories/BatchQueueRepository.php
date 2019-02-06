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
class BatchQueueRepository extends QueueRepository
{
    /**
     * @var int $batchSize
     * - Default value is 0 which disables this feature
     */
    protected $batchSize = 0;

    /**
     * @var Model $queue
     * - The queue is an instance of Laravel's Model class
     */
    public $queue;

    /**
     * Class controller for the thread batch
     * @var string $class
     */
    protected $class = '';

    /**
     * QueueRepository constructor.
     * @param QueueContract $queue
     */
    public function __construct(QueueContract $queue)
    {
        parent::__construct($queue);

        $this->batch = new Collection();
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

        return $this->batch->shift();
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
     * Get the class controller for the thread batch
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

}