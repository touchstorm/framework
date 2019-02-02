<?php

use Chronos\Repositories\BatchQueueRepository;
use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Illuminate\Database\Eloquent\Collection;

class MockBatchRepository extends BatchQueueRepository implements QueueRepositoryContract
{
    protected $connection = 'sqlite';
    protected $table = 'queue';
    protected $batchSize = 4;
    protected $maxThreads = 2;

    /**
     * On batched threads this is a static variable not polymorphic
     * like a normal running thread
     * @var string $class
     */
    protected $class = 'MockBatchThreadController';

    /**
     * FooRepository constructor.
     * @param MockBatchQueue $queue
     */
    public function __construct(MockBatchQueue $queue)
    {
        parent::__construct($queue);
    }

    /**
     * @param array $ids
     * @return Collection
     */
    public function items(array $ids = [])
    {
        return $this->mockItems($ids);
    }

    /**
     * @param $ids
     * @return Collection
     */
    private function mockItems($ids)
    {
        $collection = new Collection();

        foreach ($ids as $id) {

            $queue = clone $this->queue;
            $queue->fill([
                'id' => $id
            ]);

            $collection->push($queue);
        }

        return $collection;
    }


}
