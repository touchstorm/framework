<?php

use Chronos\TaskMaster\Dispatcher;
use Chronos\TaskMaster\Watcher;
use Chronos\Tasks\Scheduled;
use Chronos\Tasks\TaskCollector;
use Chronos\Tasks\TaskFactory;
use PHPUnit\Framework\TestCase;

if (!defined('CURRENT_TIME')) {
    define('CURRENT_TIME', (new DateTime)->format('Y-m-d H:i:s'));
}

class WatcherTest extends TestCase
{
    /**
     * @covers Watcher::dispatch();
     * @covers Watcher::execute()
     * @covers Watcher::collectDispatchedTask();
     * @covers Watcher::collectOutputs();
     * @covers Watcher::output();
     * @covers Watcher::dispatchedTask();
     * @covers Watcher::isRunning()
     * @covers Watcher::getTask()
     */
    public function testWatcher()
    {
        $collection = new TaskCollector(new TaskFactory());

        $on = [
            'server1'
        ];

        putenv('APP_SERVER=server1');

        // Set up tasks
        $collection->running('runFoo', [
            'service' => 'RunningService',
            'on' => $on
        ]);

        // Set up tasks
        $collection->batch('batchFoo', [
            'service' => 'BatchService',
            'on' => $on
        ]);

        $collection->scheduled('helloBlank')->everyMinute();

        // New up the dispatcher
        $dispatcher = new Watcher($collection);

        $this->assertFalse($dispatcher->getDryRun());

        $dispatcher->dispatch([
            'setDryRun' => true,
//            'setVerbose' => true
        ]);

        $runningTask = $dispatcher->getTask('runFoo');
        $batchTask = $dispatcher->getTask('batchFoo');

        $this->assertSame($collection->getTask('runFoo'), $runningTask);
        $this->assertSame($collection->getTask('batchFoo'), $batchTask);
        $this->assertTrue($dispatcher->getDryRun());
        $this->assertNotNull($dispatcher->dispatchedTasks());
        $this->assertNotNull($dispatcher->dispatchedTasks());
        $this->assertCount(2, $dispatcher->dispatchedTasks());
        $this->assertCount(0, $dispatcher->batchTasks()); // should be empty since they dispatched and weren't found live
        $this->assertCount(0, $dispatcher->runningTasks());// should be empty since they dispatched and weren't found live
    }
}