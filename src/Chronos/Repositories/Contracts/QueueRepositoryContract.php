<?php

namespace Chronos\Repositories\Contracts;

/**
 * Interface QueueRepositoryContract
 * @package Chronos\Repositories\Contracts
 */
interface QueueRepositoryContract
{
    /**
     * @param $id
     * @return \Chronos\Queues\Queue
     */
    public function item($id);

    /**
     * Get next Queue
     * @return \Chronos\Queues\Queue
     */
    public function next();

    /**
     * @param mixed $options
     * @param array $fields
     * @return mixed
     */
    public function reset($options, $fields);

    /**
     * @param $options
     * @return mixed
     */
    public function fill($options);

    /**
     * @return mixed
     */
    public function setBatchInUse();

    /**
     * @return mixed
     */
    public function isSleeping();

    /**
     * @return mixed required for threads
     * to work.
     */
    public function getMaxThreads();

    /**
     * @return mixed required for treads
     * to work.
     */
    public function getBatchSize();
}