<?php

namespace Chronos\Queues;

use Closure;
use DateTime;

/**
 * Class Queue abstract to create custom Queue types
 * @package Chronos\Queues
 */
abstract class Queue
{
    /**
     * Default vector arguments the Thread dispatcher
     * will use to pass to the individual Threads
     * @return array
     */
    abstract public function threadArguments();

    /**
     * Fetch a batch of queues off our data source
     * @param int $maxThreads
     * @param Closure $options
     * @return mixed
     */
    abstract public function fetch(int $maxThreads, Closure $options);

    /**
     * Set the batch of queues in use
     * @param array $ids
     */
    abstract public function setInUse(array $ids);

    /**
     * Reset the current queue item
     * @param null|array|int|string|Closure $options
     * @param array $fields
     */
    abstract public function reset($options, array $fields);

    /**
     * Reschedule the queue item for its next run
     * default +24 hours from now.
     * @param null|string|DateTime $date
     */
    abstract public function reschedule($date);

    /**
     * Complete the queue item & reschedule (optional)
     * @param bool $reschedule
     * @param null|string|DateTime $date
     */
    abstract public function completed($reschedule, $date);

    /**
     * Self delete from queue.
     */
    abstract public function remove();

}