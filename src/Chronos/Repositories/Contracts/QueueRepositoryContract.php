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
    function item($id);

    /**
     * @param $ids
     * @return mixed
     */
    function items(array $ids);

    /**
     * Get next Queue
     * @return \Chronos\Queues\Queue
     */
    function next();

    /**
     * @param mixed $options
     * @param array $fields
     * @return mixed
     */
    function reset($options, $fields);

    /**
     * @param $options
     * @return mixed
     */
    function fill($options);

    /**
     * @return mixed
     */
    function setBatchInUse();

    /**
     * @return mixed
     */
    function isSleeping();

    /**
     * @return mixed required for threads
     * to work.
     */
    function getMaxThreads();

    /**
     * @return mixed required for treads
     * to work.
     */
    function getBatchSize();
}