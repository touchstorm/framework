<?php

namespace Chronos\Queues;

use Closure;
use DateTime;

/**
 * TODO
 * Class SessionQueue
 * @package Chronos\Queues
 */
class SessionQueue extends Queue
{

    /**
     * Default vector arguments the Thread dispatcher
     * will use to pass to the individual Threads
     * @return array
     */
    public function threadArguments()
    {
        // TODO: Implement threadArguments() method.
    }

    /**
     * Fetch a batch of queues off our data source
     * @param int $maxThreads
     * @param Closure $options
     * @return mixed
     */
    public function fetch(int $maxThreads, Closure $options)
    {
        // TODO: Implement fetch() method.
    }

    /**
     * Set the batch of queues in use
     * @param array $ids
     */
    public function setInUse(array $ids)
    {
        // TODO: Implement setInUse() method.
    }

    /**
     * Reset the current queue item
     * @param null|array|int|string|Closure $options
     * @param array $fields
     */
    public function reset($options, array $fields)
    {
        // TODO: Implement reset() method.
    }

    /**
     * Reschedule the queue item for its next run
     * default +24 hours from now.
     * @param null|string|DateTime $date
     */
    public function reschedule($date)
    {
        // TODO: Implement reschedule() method.
    }

    /**
     * Complete the queue item & reschedule (optional)
     * @param bool $reschedule
     * @param null|string|DateTime $date
     */
    public function completed($reschedule, $date)
    {
        // TODO: Implement completed() method.
    }

    /**
     * Self delete from queue.
     */
    public function remove()
    {
        // TODO: Implement remove() method.
    }
}