<?php

namespace Chronos\Repositories\Contracts;

/**
 * Interface QueueRepositoryContract
 * @package Chronos\Application\Repositories\Contracts
 */
interface QueueRepositoryContract
{
    /**
     * @param $id
     * @return \Chronos\Application\Queues\Queue
     */
    function item($id);

    /**
     * Get next Queue
     * @return \Chronos\Application\Queues\Queue
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