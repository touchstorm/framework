<?php

use Chronos\Repositories\BatchQueueRepository;
use Chronos\Repositories\Contracts\QueueRepositoryContract;

class MockBatchRepository extends BatchQueueRepository implements QueueRepositoryContract
{
    protected $connection = 'sqlite';
    protected $table = 'queue';
    protected $batchSize = 4;
    protected $maxThreads = 2;

    /**
     * FooRepository constructor.
     * @param MockBatchQueue $queue
     */
    public function __construct(MockBatchQueue $queue)
    {
        parent::__construct($queue);
    }
}
