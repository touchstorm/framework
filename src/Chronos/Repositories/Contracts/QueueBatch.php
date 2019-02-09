<?php

namespace Chronos\Repositories\Contracts;

/**
 * Interface QueueRepositoryContract
 * @package Chronos\Repositories\Contracts
 */
interface QueueBatch extends QueueRepositoryContract
{
    /**
     * Full pulling batches of items
     * @param $ids
     * @return mixed
     */
    function items(array $ids);
}