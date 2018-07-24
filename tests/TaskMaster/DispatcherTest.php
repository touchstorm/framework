<?php

use Chronos\TaskMaster\Dispatcher;
use Chronos\Tasks\Scheduled;
use Chronos\Tasks\TaskCollector;
use Chronos\Tasks\TaskFactory;
use PHPUnit\Framework\TestCase;

if (!defined('CURRENT_TIME')) {
    define('CURRENT_TIME', (new DateTime)->format('Y-m-d H:i:s'));
}

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
        $this->assertSame("Hello World", $dispatcher->output($task)[0][0]);

        // Task in dispatched container
        $this->assertSame($task, $dispatcher->dispatchedTask($task)[0]['task']);
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

        $collection->scheduled('scheduledRunningTaskTest', [
            // Command will log scheduledTaskTest and sleep for 10 seconds
            'command' => "php -r 'echo \"scheduledRunningTaskTest\"; sleep(10);'",
            'at' => function (Scheduled $task) {
                return $task->everyMinute();
            },
            'on' => $on,
            'async' => true
        ]);

        // New up the dispatcher
        $dispatcher = new Dispatcher($collection);

        // Extract the task
        $task = $collection->getTask('scheduledRunningTaskTest');

        $this->assertSame($collection->getTask('scheduledRunningTaskTest'), $dispatcher->getTask($task));

        // Containers should all be empty
        $this->assertEmpty($dispatcher->dispatchedTasks());
        $this->assertEmpty($dispatcher->dormantTasks());
        $this->assertEmpty($dispatcher->outputs());

        // Fire dispatcher
        // Script will echo scheduledTaskTest
        $dispatcher->dispatch();

        // IS RUNNING AS A PROCESS!!
        $this->assertTrue((bool)$dispatcher->isRunning($task));

        // Task in dispatched container
        $this->assertNotEmpty($dispatcher->dispatchedTasks());
        $this->assertSame($task, $dispatcher->dispatchedTask($task)[0]['task']);
    }

    /**
     * @covers Dispatcher::dispatch();
     * @covers Dispatcher::output();
     * @covers Dispatcher::dispatchedTask();
     * @covers Dispatcher::isRunning()
     * @covers Dispatcher::getTask()
     * @throws \Chronos\Tasks\Exceptions\TaskCollectionException
     */
    public function testScheduledRunningControllerDispatch()
    {
        $collection = new TaskCollector(new TaskFactory());

        $on = [
            'server1',
            'server2',
            'server3',
            'server4'
        ];

        putenv('APP_SERVER=server1');

        $collection->scheduled('scheduledControllerMethodTest', [

            'uses' => "scheduledControllerMethodTest@method",
            'at' => function (Scheduled $task) {
                return $task->everyMinute();
            },
            'on' => $on
        ]);

        // New up the dispatcher
        $dispatcher = new Dispatcher($collection);

        // Extract the task
        $task = $collection->getTask('scheduledControllerMethodTest');

        $this->assertSame($collection->getTask('scheduledControllerMethodTest'), $dispatcher->getTask($task));

        // Containers should all be empty
        $this->assertEmpty($dispatcher->dispatchedTasks());
        $this->assertEmpty($dispatcher->dormantTasks());
        $this->assertEmpty($dispatcher->outputs());

        // Fire dispatcher
        // Script will echo scheduledTaskTest
        $dispatcher->dispatch();

        // Task in dispatched container
        $this->assertNotEmpty($dispatcher->dispatchedTasks());
        $this->assertSame($task, $dispatcher->dispatchedTask($task)[0]['task']);
    }

    /**
     * @covers Dispatcher::dispatch();
     * @covers Dispatcher::execute()
     * @covers Dispatcher::collectDispatchedTask();
     * @covers Dispatcher::collectOutputs();
     * @covers Dispatcher::output();
     * @covers Dispatcher::dispatchedTask();
     * @covers Dispatcher::isRunning()
     * @covers Dispatcher::getTask()
     * @throws \Chronos\Tasks\Exceptions\TaskCollectionException
     */
    public function testScheduledBeforeAndAfter()
    {
        $collection = new TaskCollector(new TaskFactory());

        $on = [
            'server1',
            'server2',
            'server3',
            'server4'
        ];

        $before = [
            'echo before',
            'echo before before'
        ];

        $after = [
            'echo after',
            'echo after after'
        ];

        putenv('APP_SERVER=server1');

        $collection->scheduled('testScheduledBeforeAndAfter', [
            'uses' => "testScheduledBeforeAndAfter@method",
            'at' => function (Scheduled $task) {
                return $task->everyMinute();
            },
            'on' => $on
        ])->before($before)->after($after);

        // New up the dispatcher
        $dispatcher = new Dispatcher($collection);

        // Extract the task
        $task = $collection->getTask('testScheduledBeforeAndAfter');

        $this->assertSame($collection->getTask('testScheduledBeforeAndAfter'), $dispatcher->getTask($task));

        // Containers should all be empty
        $this->assertEmpty($dispatcher->dispatchedTasks());
        $this->assertEmpty($dispatcher->dormantTasks());
        $this->assertEmpty($dispatcher->outputs());

        // Fire dispatcher
        // Script will echo scheduledTaskTest
        $dispatcher->dispatch();

        // Before output
        $this->assertSame('before', $dispatcher->output($task, 'before')[0][0]);
        $this->assertSame('before before', $dispatcher->output($task, 'before')[1][0]);

        // After output
        $this->assertSame('after', $dispatcher->output($task, 'after')[0][0]);
        $this->assertSame('after after', $dispatcher->output($task, 'after')[1][0]);

        // Task in dispatched container
        $this->assertNotEmpty($dispatcher->dispatchedTasks());
        $this->assertSame($task, $dispatcher->dispatchedTask($task)[0]['task']);

        // Assert the before commands
        $this->assertSame($before[0], $dispatcher->dispatchedTask($task, 'before')[0]['command']);
        $this->assertSame($before[1], $dispatcher->dispatchedTask($task, 'before')[1]['command']);

        // Assert the after commands
        $this->assertSame($after[0], $dispatcher->dispatchedTask($task, 'after')[0]['command']);
        $this->assertSame($after[1], $dispatcher->dispatchedTask($task, 'after')[1]['command']);

    }

    /**
     * @covers Dispatcher::dispatch();
     * @covers Dispatcher::execute()
     * @covers Dispatcher::collectDispatchedTask();
     * @covers Dispatcher::collectOutputs();
     * @covers Dispatcher::output();
     * @covers Dispatcher::dispatchedTask();
     * @covers Dispatcher::isRunning()
     * @covers Dispatcher::getTask()
     * @throws \Chronos\Tasks\Exceptions\TaskCollectionException
     */
    public function testScheduledMulti()
    {
        $collection = new TaskCollector(new TaskFactory());

        $on = [
            'server1',
            'server2',
            'server3',
            'server4'
        ];

        $before = [
            'echo before',
            'echo before before'
        ];

        $after = [
            'echo after',
            'echo after after'
        ];

        putenv('APP_SERVER=server1');

        // Set up tasks
        $collection->scheduled('helloWorld', [
            'command' => "echo Hello World",
            'at' => function (Scheduled $task) {
                return $task->everyMinute();
            },
            'on' => $on
        ])->before($before)->after($after);

        $collection->scheduled('helloUniverse', [
            'command' => 'echo Hello Universe',
            'at' => function (Scheduled $task) {
                return $task->everyMinute();
            },
            'on' => $on
        ])->before($before)->after($after);

        $collection->scheduled('helloBlank')->everyMinute()->before($before)->after($after);

        // New up the dispatcher
        $dispatcher = new Dispatcher($collection);

        // Extract the task
        $hw = $collection->getTask('helloWorld');
        $hu = $collection->getTask('helloUniverse');

        // Assert collections
        $this->assertSame($collection->getTask('helloWorld'), $dispatcher->getTask($hw));
        $this->assertSame($collection->getTask('helloUniverse'), $dispatcher->getTask($hu));

        // Containers should all be empty
        $this->assertEmpty($dispatcher->dispatchedTasks());
        $this->assertEmpty($dispatcher->dormantTasks());
        $this->assertEmpty($dispatcher->outputs());

        // Fire dispatcher
        // Script will echo scheduledTaskTest
        $dispatcher->dispatch();

        // Before output
        $this->assertSame('before', $dispatcher->output($hw, 'before')[0][0]);
        $this->assertSame('before', $dispatcher->output($hu, 'before')[0][0]);

        $this->assertSame('before before', $dispatcher->output($hw, 'before')[1][0]);
        $this->assertSame('before before', $dispatcher->output($hu, 'before')[1][0]);

        // Command output
        $this->assertSame('Hello World', $dispatcher->output($hw)[0][0]);
        $this->assertSame('Hello Universe', $dispatcher->output($hu)[0][0]);

        // After output
        $this->assertSame('after', $dispatcher->output($hw, 'after')[0][0]);
        $this->assertSame('after', $dispatcher->output($hu, 'after')[0][0]);

        $this->assertSame('after after', $dispatcher->output($hw, 'after')[1][0]);
        $this->assertSame('after after', $dispatcher->output($hu, 'after')[1][0]);

        // Task in dispatched container
        $this->assertNotEmpty($dispatcher->dispatchedTasks());
        $this->assertSame($hw, $dispatcher->dispatchedTask($hw)[0]['task']);
        $this->assertSame($hu, $dispatcher->dispatchedTask($hu)[0]['task']);

        // Assert the before commands
        $this->assertSame($before[0], $dispatcher->dispatchedTask($hw, 'before')[0]['command']);
        $this->assertSame($before[1], $dispatcher->dispatchedTask($hu, 'before')[1]['command']);

        $this->assertSame('echo Hello World', $dispatcher->dispatchedTask($hw)[0]['command']);
        $this->assertSame("echo Hello Universe", $dispatcher->dispatchedTask($hu)[0]['command']);

        // Assert the after commands
        $this->assertSame($after[0], $dispatcher->dispatchedTask($hw, 'after')[0]['command']);
        $this->assertSame($after[1], $dispatcher->dispatchedTask($hu, 'after')[1]['command']);

    }
}