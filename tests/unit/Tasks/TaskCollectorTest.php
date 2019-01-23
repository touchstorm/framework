<?php

use Chronos\Tasks\Scheduled;
use Chronos\Tasks\TaskCollector;
use Chronos\Tasks\TaskFactory;
use PHPUnit\Framework\TestCase;

class TaskCollectorTest extends TestCase
{
    /**
     * @covers TaskCollector::scheduled()
     * @covers TaskCollector::running()
     * @covers TaskCollector::getTasks()
     */
    public function testTaskCollector()
    {
        $tasks = new TaskCollector(new TaskFactory());

        $on = [
            'server1',
            'server2',
            'server3',
            'server4'
        ];

        putenv('APP_SERVER=server1');

        $tasks->running('runningTaskTest', [
            'uses' => 'CoreController',
            'on' => $on
        ]);

        $tasks->scheduled('scheduledTaskTest', [
            'command' => 'ls -la',
            'at' => function (Scheduled $task) {
                return $task->everyMinute();
            },
            'on' => $on
        ]);

        /**
         * @var \Chronos\Tasks\Task $task
         */
        foreach ($tasks->getTasks() as $name => $task) {
            $this->assertInstanceOf(\Chronos\Tasks\Task::class, $task);
            $this->assertSame($name, $task->getName());
        }
    }

    /**
     * @covers TaskCollector::getTask()
     */
    public function testTaskCollectorCollectionRetrieval()
    {
        $tasks = new TaskCollector(new TaskFactory());

        $on = [
            'server1',
            'server2',
            'server3',
            'server4'
        ];

        putenv('APP_SERVER=server1');

        $tasks->scheduled('scheduledTaskTest', [
            'command' => 'ls -la',
            'at' => function (Scheduled $task) {
                return $task->everyMinute();
            },
            'on' => $on
        ]);

        // Coverage
        $task = $tasks->getTask('scheduledTaskTest');

        $this->assertInstanceOf(\Chronos\Tasks\Task::class, $task);
        $this->assertSame('scheduledTaskTest', $task->getName());
        $this->assertSame('ls -la', $task->getCommand()[0]);
    }
}