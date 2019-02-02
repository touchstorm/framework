<?php

namespace Chronos\Repositories\Contracts;

/**
 * Interface QueueRepositoryContract
 * @package Chronos\Repositories\Contracts
 */
interface QueueBatch extends QueueRepositoryContract
{
    /**
     * @param $ids
     * @return mixed
     */
    function items(array $ids);

}