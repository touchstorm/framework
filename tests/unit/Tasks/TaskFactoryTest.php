<?php

use Chronos\Tasks\TaskFactory;
use PHPUnit\Framework\TestCase;

class TaskFactoryTest extends TestCase
{

    /**
     * @covers TaskFactory::Scheduled
     */
    public function testTaskFactoryMakeScheduledTask()
    {
        $factory = new TaskFactory();

        $arguments = [
            'command' => 'ls -la',
            'at' => function (\Chronos\Tasks\Scheduled $task) {
                return $task->everyMinute();
            }
        ];

        $scheduled = $factory->scheduled('testScheduled', $arguments);

        $this->assertSame('testScheduled', $scheduled->getName());
        $this->assertSame($arguments['command'], $scheduled->getCommand()[0]);
        $this->assertSame('scheduled', $scheduled->getType());

        // Defaults to every minute when created in the factory
        $this->assertSame('* * * * *', $scheduled->runs);
    }

    /**
     * @covers TaskFactory::Running
     */
    public function testTaskFactoryMakeRunningTask()
    {
        $factory = new TaskFactory();

        $arguments = [
            'uses' => 'testService'
        ];

        $scheduled = $factory->running('testRunning', $arguments);

        $this->assertSame('testRunning', $scheduled->getName());
        $this->assertSame($arguments['uses'], $scheduled->getService());

    }
}