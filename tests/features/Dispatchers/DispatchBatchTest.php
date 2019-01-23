<?php

use Chronos\Dispatchers\Batches;
use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\Queue;
use Chronos\Repositories\BatchQueueRepository;
use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class MockBatchQueue extends Queue implements QueueContract
{
    protected $connection = 'sqlite';
}

class MockBatchRepository extends BatchQueueRepository implements QueueRepositoryContract
{
    protected $connection = 'sqlite';
    protected $table = 'queue';
    protected $batchSize = 4;
    protected $maxThreads = 2;
}

class DispatchBatchTest extends TestCase
{
    /**
     * @covers \Chronos\Dispatchers\Batches::setVerbose
     * @covers \Chronos\Dispatchers\Batches::runUntilEmpty
     * @covers \Chronos\Dispatchers\Batches::setEmptied
     * @covers \Chronos\Dispatchers\Batches::setDryRun
     * @covers \Chronos\Dispatchers\Batches::getEmptied
     * @covers \Chronos\Dispatchers\Batches::getDryRun
     * @covers \Chronos\Dispatchers\Batches::getVerbose
     */
    public function testSettersAndGetters()
    {
        // Build a Eloquent collection of
        // 50 Queues
        $batch = $this->buildBatch(100);

        // Get the first queue to inject into the repository
        $queue = $batch->first();

        // Create the repository and inject the queue
        $repository = new MockBatchRepository($queue);

        $repository->fill($batch);

        // Create the dispatcher
        $dispatcher = new Batches($repository);

        // Setters
        $dispatcher->setDryRun(true);
        $dispatcher->runUntilEmpty();
        $dispatcher->setVerbose(true);

        // Asserts
        $this->assertTrue($dispatcher->getDryRun());
        $this->assertTrue($dispatcher->getEmptied());
        $this->assertTrue($dispatcher->getVerbose());

        // Setters
        $dispatcher->setDryRun(false);
        $dispatcher->setEmptied(false);
        $dispatcher->setVerbose(false);

        // Asserts
        $this->assertFalse($dispatcher->getDryRun());
        $this->assertFalse($dispatcher->getEmptied());
        $this->assertFalse($dispatcher->getVerbose());

    }

    /**
     * @covers \Chronos\Dispatchers\Batches::getProcesses
     * @covers \Chronos\Dispatchers\Batches::getProcessIds
     */
    public function testRepositoryBatchNextMethod()
    {
        // Build a Eloquent collection of
        // 50 Queues
        $batch = $this->buildBatch(100);

        // Get the first queue to inject into the repository
        $queue = $batch->first();

        // Create the repository and inject the queue
        $repository = new MockBatchRepository($queue);

        $repository->fill($batch);

        // Create the dispatcher
        $dispatcher = new Batches($repository);
        $dispatcher->setDryRun(true);
        $dispatcher->runUntilEmpty();
        $dispatcher->setVerbose(false);

        // Handle the batched threads. Breaks out on dryRun(true)
        $dispatcher->handle();

        // Assert all processes completed
        $this->assertEmpty($dispatcher->getProcessIds());
        $this->assertEmpty($dispatcher->getProcesses());

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
        $queue = new MockBatchQueue();

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