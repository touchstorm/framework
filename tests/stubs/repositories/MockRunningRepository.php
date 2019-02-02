<?php

use Chronos\Repositories\BatchQueueRepository;
use Chronos\Repositories\Contracts\QueueBatch;

class MockRunningRepository extends BatchQueueRepository implements QueueBatch
{
    protected $connection = 'sqlite';
    protected $table = 'queue';
    protected $maxThreads = 10;

    /**
     * MockRunningRepository constructor.
     * @param MockRunningQueue $queue
     */
    public function __construct(MockRunningQueue $queue)
    {
        parent::__construct($queue);
    }

}