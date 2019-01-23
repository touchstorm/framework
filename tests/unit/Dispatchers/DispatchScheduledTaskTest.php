<?php

use Chronos\Dispatchers\Threads;
use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\Queue;
use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Chronos\Repositories\QueueRepository;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class MockQueue extends Queue implements QueueContract
{
}

class MockRepository extends QueueRepository implements QueueRepositoryContract
{
    protected $connection = 'engine';
    protected $table = 'queue';
}

class DispatchScheduledTaskTest extends TestCase
{
    // Generate 100 queues
    //

    public function testDispatchScheduled()
    {
        $this->assertSame(null,null);
    }

    private function Scheduled()
    {
        // Build a Eloquent collection of
        // 50 Queues
        $batch = $this->buildQueueBatch(50);

        // Get the first queue to inject into the repository
        $queue = $batch->first();

        // Create the repository and inject the queue
        $repository = new MockRepository($queue);

        // Fill the queue's batch with all 50 Queues
        $repository->batch = (new Collection())->push($queue);

        // Create the thread dispatcher
        $thread = new Threads($repository);

    }

    private function buildQueueBatch($items = 50)
    {
        $collection = new Collection();

        for ($x = 1; $x <= $items; $x++) {
            $collection->push(new MockQueue([
                'id' => $x,
                'in_use' => 0,
                'priority' => 1,
                'available_at' => (new DateTime())->modify('-10 minutes')->format('Y-m-d H:i:s'),
                'completed_at' => null
            ]));
        }

        return $collection;
    }
}