<?php

use Chronos\Dispatchers\Batches;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

class BatchDispatcherTest extends TestCase
{
    /**
     * @covers \Chronos\Dispatchers\Batches::setCollectCommands
     * @covers \Chronos\Dispatchers\Batches::setDryRun
     * @covers \Chronos\Dispatchers\Batches::runUntilEmpty
     * @covers \Chronos\Dispatchers\Batches::setVerbose
     * @covers \Chronos\Dispatchers\Batches::handle
     * @covers \Chronos\Dispatchers\Batches::getProcessIds
     * @covers \Chronos\Dispatchers\Batches::getProcesses
     * @covers \Chronos\Dispatchers\Batches::getDispatchedCommands
     * @covers \Chronos\Dispatchers\Batches::getDispatchedCommand
     */
    public function testBatchDispatcher()
    {
        $batch = $this->buildBatch(100);

        $repository = new MockBatchRepository($batch->first());
        $repository->fill($batch);

        $dispatcher = new Batches($repository);

        $dispatcher->setCollectCommands(true);
        $dispatcher->setDryRun(true);
        $dispatcher->runUntilEmpty();
        $dispatcher->setVerbose(false);

        $dispatcher->handle();

        $this->assertEmpty($dispatcher->getProcessIds());
        $this->assertEmpty($dispatcher->getProcesses());
        $this->assertCount(100 / 4, $dispatcher->getDispatchedCommands());
        $this->assertStringEndsWith('1~2~3~4"', $dispatcher->getDispatchedCommand(0));
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