<?php

use Chronos\TaskMaster\Dispatcher;
use Chronos\Tasks\Scheduled;
use Chronos\Tasks\TaskCollector;
use Chronos\Tasks\TaskFactory;
use PHPUnit\Framework\TestCase;

define('CURRENT_TIME', (new DateTime)->format('Y-m-d H:i:s'));

class DispatcherTest extends TestCase
{
    /**
     * @covers Dispatcher::dispatch();
     * @covers Dispatcher::output();
     * @covers Dispatcher::dispatchedTask();
     * @throws \Chronos\Tasks\Exceptions\TaskCollectionException
     */
    public function testScheduledDispatch()
    {
        $collection = new TaskCollector(new TaskFactory());

        $on = [
            'server1',
            'server2',
            'server3',
            'server4'
        ];

        putenv('APP_SERVER=server1');

        $collection->scheduled('scheduledTaskTest', [
            // Command will log scheduledTaskTest and sleep for 10 seconds
            'command' => "php -r 'echo \"Hello World\";'",
            'at' => function (Scheduled $task) {
                return $task->everyMinute();
            },
            'on' => $on
        ]);

        // New up the dispatcher
        $dispatcher = new Dispatcher($collection);

        $task = $collection->getTask('scheduledTaskTest');

        $this->assertSame($collection->getTask('scheduledTaskTest'), $dispatcher->getTask($task));

        // Containers should all be empty
        $this->assertEmpty($dispatcher->dispatchedTasks());
        $this->assertEmpty($dispatcher->dormantTasks());
        $this->assertEmpty($dispatcher->outputs());

        // Fire dispatcher
        // Script will echo scheduledTaskTest
        $dispatcher->dispatch();

        // The name of the task and log should be the same
        $this->assertSame("Hello World", $dispatcher->output($task)[0]);

        // Task in dispatched container
        $this->assertSame($task, $dispatcher->dispatchedTask($task));
    }

    /**
     * @covers Dispatcher::dispatch();
     * @covers Dispatcher::output();
     * @covers Dispatcher::dispatchedTask();
     * @covers Dispatcher::isRunning()
     * @covers Dispatcher::getTask()
     * @throws \Chronos\Tasks\Exceptions\TaskCollectionException
     */
    public function testScheduledRunningDispatch()
    {
        $collection = new TaskCollector(new TaskFactory());

        $on = [
            'server1',
            'server2',
            'server3',
            'server4'
        ];

        putenv('APP_SERVER=server1');

        $collection->scheduled('scheduledTaskTest', [
            // Command will log scheduledTaskTest and sleep for 10 seconds
            'command' => "nohup php -r 'echo \"scheduledTaskTest\"; sleep(1);' > /dev/null 2>&1 &",
            'at' => function (Scheduled $task) {
                return $task->everyMinute();
            },
            'on' => $on
        ]);

        // New up the dispatcher
        $dispatcher = new Dispatcher($collection);

        // Extract the task
        $task = $collection->getTask('scheduledTaskTest');

        $this->assertSame($collection->getTask('scheduledTaskTest'), $dispatcher->getTask($task));

        // Containers should all be empty
        $this->assertEmpty($dispatcher->dispatchedTasks());
        $this->assertEmpty($dispatcher->dormantTasks());
        $this->assertEmpty($dispatcher->outputs());

        // Fire dispatcher
        // Script will echo scheduledTaskTest
        $dispatcher->dispatch();

        // IS RUNNING AS A PROCESS!!
        $this->assertTrue($dispatcher->isRunning($task->getName()));

        // Task in dispatched container
        $this->assertNotEmpty($dispatcher->dispatchedTasks());
        $this->assertSame($task, $dispatcher->dispatchedTask($task));
    }

}