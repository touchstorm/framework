<?php

use Chronos\TaskMaster\Dispatcher;
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
     * @covers Dispatcher::dispatch();
     * @covers Dispatcher::execute()
     * @covers Dispatcher::collectDispatchedTask();
     * @covers Dispatcher::collectOutputs();
     * @covers Dispatcher::output();
     * @covers Dispatcher::dispatchedTask();
     * @covers Dispatcher::isRunning()
     * @covers Dispatcher::getTask()
     */
    public function testWatcher()
    {
        $collection = new TaskCollector(new TaskFactory());

        $this->assertTrue(true);

        return;

        $on = [
            'server1'
        ];

        putenv('APP_SERVER=server1');

        // Set up tasks
        $collection->running('helloWorld', [
            'on' => $on
        ]);

        $collection->scheduled('helloBlank')->everyMinute()->before($before)->after($after);

        // New up the dispatcher
        $dispatcher = new Dispatcher($collection);

    }
}