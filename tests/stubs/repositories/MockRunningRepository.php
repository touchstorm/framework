<?php

use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Chronos\Repositories\QueueRepository;

class MockRunningRepository extends QueueRepository implements QueueRepositoryContract
{
    protected $connection = 'sqlite';
    protected $table = 'queue';
    protected $maxThreads = 10;

    /**
     * FooRepository constructor.
     * @param MockRunningQueue $queue
     */
    public function __construct(MockRunningQueue $queue)
    {
        parent::__construct($queue);
    }
}