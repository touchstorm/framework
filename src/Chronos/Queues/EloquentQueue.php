<?php

namespace Chronos\Queues;

use Closure;
use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EloquentQueue
 * @package Chronos\Queues
 */
class EloquentQueue extends Model
{
    /**
     * By default Chronos queues do not use
     * Eloquent's default timestamps
     * @var bool
     */
    public $timestamps = false;

    /**
     * Default fields required by all Queues
     * @var array
     */
    protected $fillable = [
        'in_use',
        'priority',
        'available_at',
        'completed_at'
    ];

    /**
     * Default vector arguments the Thread dispatcher
     * will use to pass to the individual Threads
     * @return array
     */
    public function threadArguments()
    {
        return [
            'id' => $this->getAttribute('id')
        ];
    }

    /**
     * Get thread arguments
     * @param $key
     * @return mixed|null
     */
    public function getThreadArgument($key)
    {
        return $this->threadArguments()[$key] ?? null;
    }

    /**
     * Fetch a batch of queues off our data source
     * @param int $maxThreads
     * @param Closure $options
     * @return mixed
     */
    public function fetch($maxThreads = 1, Closure $options)
    {
        // Set a limit. We'll fetch more queues
        // than are needed so we aren't making
        // excess requests to the database
        $limit = $$maxThreads * 4;

        // Fill the batch from the queue
        $batch = $this
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
        return $batch->get();
    }

    /**
     * Set the batch in use
     * @param array $ids
     */
    public function setInUse(array $ids = [])
    {
        // Update the batch's items to be in use
        $this->whereIn('id', $ids)->update(['in_use' => 1]);
    }

    /**
     * Reset the queue item
     * @param null|array|int|string|Closure $options
     * @param array $fields
     */
    public function reset($options = null, $fields = [])
    {
        $queue = $this;

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
     * Reschedule the queue item for its next run
     * default +24 hours from now.
     * @param $date
     */
    public function reschedule($date = null)
    {
        // Default is always +24 hours
        if (!$date instanceof DateTime) {
            $date = new DateTime(((is_null($date)) ? '+24 hours' : $date));
        }

        // Update the queue
        $this->where('id', $this->getAttribute('id'))
            ->update([
                'available_at' => $date->format('Y-m-d H:i:s'),
                'in_use' => 0
            ]);
    }

    /**
     * Complete the queue item & reschedule (optional)
     * @param bool $reschedule
     * @param null $date
     */
    public function completed($reschedule = true, $date = null)
    {
        $this->where('id', $this->getAttribute('id'))
            ->update([
                'in_use' => 0,
                'completed_at' => (new DateTime())->format('Y-m-d H:i:s')
            ]);

        if ($reschedule) {
            $this->reschedule($date);
        }
    }

    /**
     * Self delete from queue.
     */
    public function remove()
    {
        $this->delete();
    }

}