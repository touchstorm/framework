<?php

use Chronos\Dispatchers\Threads;
use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\Queue;
use Chronos\Repositories\BatchQueueRepository;
use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class MockBatchTestQueue extends Queue implements QueueContract
{
    protected $connection = 'sqlite';
}

class MockBatchTestRepository extends BatchQueueRepository implements QueueRepositoryContract
{
    protected $connection = 'sqlite';
    protected $table = 'queue';
    protected $batchSize = 4;
    protected $maxThreads = 2;
}

class DispatchBatchTaskTest extends TestCase
{
    // Generate 100 queues
    // batch them
    public function testRepositoryBatchNextMethod()
    {
        // Build a Eloquent collection of
        // 50 Queues
        $batch = $this->buildBatch(100);

        // Get the first queue to inject into the repository
        $queue = $batch->first();

        // Create the repository and inject the queue
        $repository = new MockBatchTestRepository($queue);

        $repository->fill($batch);

        $queuesBatched = $repository->next();

        // Assert if the batchSize is correct as defined in the MockBatchRepository
        $this->assertCount(4, $queuesBatched->toArray());
        $this->assertSame(4, $repository->getBatchSize());

        // Assert that it is a collection
        $this->assertInstanceOf(Collection::class, $queuesBatched);
    }

    private function buildBatch($items = 50)
    {
        $collection = new Collection();

        for ($x = 1; $x <= $items; $x++) {

            $queue = $this->createQueue($x);

            $collection->push($queue);
        }

        return $collection;
    }

    private function createQueue($x = null)
    {
        $queue = new MockBatchTestQueue();

        $queue::unguard();

        if (is_null($x)) {
            return $queue;
        }

        $queue->fill([
            'id' => $x,
            'in_use' => 0,
            'priority' => 1,
            'available_at' => (new DateTime())->modify('-' . $x . ' minutes')->format('Y-m-d H:i:s'),
            'completed_at' => null
        ]);

        return $queue;
    }
}