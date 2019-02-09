<?php

namespace Chronos\Queues\Contracts;

use Closure;

interface QueueContract
{
    /**
     * Default vector arguments the Thread dispatcher
     * will use to pass to the individual Threads
     * @return array
     */
    public function threadArguments();

    /**
     * Fetch a batch of queues off our data source
     * @param int $maxThreads
     * @param Closure $options
     * @return mixed
     */
    public function fetch($maxThreads, Closure $options);

    /**
     * Set the batch of queues in use
     * @param array $ids
     */
    public function setInUse(array $ids);

    /**
     * Reset the current queue item
     * @param null|array|int|string|Closure $options
     * @param array $fields
     */
    public function reset($options, array $fields);

    /**
     * Reschedule the queue item for its next run
     * default +24 hours from now.
     * @param null|string|DateTime $date
     */
    public function reschedule($date);

    /**
     * Complete the queue item & reschedule (optional)
     * @param bool $reschedule
     * @param null|string|DateTime $date
     */
    public function completed($reschedule, $date);

    /**
     * Self delete from queue.
     */
    public function remove();
}