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
}