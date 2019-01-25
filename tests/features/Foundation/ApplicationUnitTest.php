<?php

use Chronos\Foundation\Application;
use Chronos\Tasks\Task;
use PHPUnit\Framework\TestCase;


class AppFoo
{
    private $value;

    public function __construct($fooValue = '')
    {
        $this->value = $fooValue;
    }

    public function give()
    {
        return $this->value;
    }
}

class ApplicationUnitTest extends TestCase
{

    /**
     * @covers \Chronos\Foundation\Application::registerTasks
     * @throws \Auryn\InjectionException
     */
    public function testApplicationTaskResolve()
    {
        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'features' . DIRECTORY_SEPARATOR . 'Foundation';

        // new up and extend application
        // on construct the application will register the tasks
        // tasks are collected by the TaskCollector that injects a TaskFactory
        // in the ../tasks/ folder ('Hi', 'blank')
        $app = new class($dir) extends Application
        {
        };

        // Resolve the dispatcher out of the application
        // Dispatchers resolves the TaskCollector when made through the IoC
        // If the IoC is injecting dependencies properly then our tasks should
        // be retrievable.
        $dispatcher = $app->make(\Chronos\TaskMaster\Dispatcher::class);

        $this->assertNotEmpty($dispatcher->getTasks());
        $this->assertInstanceOf(Task::class, $dispatcher->getTask('Foo'));
        $this->assertInstanceOf(Task::class, $dispatcher->getTask('Bar'));

        // Watchers dispatch our non blocking i/o nohup tasks
        // like the Dispatcher it extends the BaseTaskMaster and the TaskCollector
        // as an injected dependency
        $watcher = $app->make(\Chronos\TaskMaster\Watcher::class);

        $this->assertNotEmpty($watcher->getTasks());
        $this->assertInstanceOf(Task::class, $watcher->getTask('runFoo'));

    }

    /**
     * @covers \Chronos\Foundation\Application::register
     * @throws \Auryn\InjectionException
     */
    public function testApplicationRegisterServiceProvider()
    {
        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'features' . DIRECTORY_SEPARATOR . 'Foundation';

        $app = new class($dir) extends Application
        {
        };

        $provider = new class($app) extends \Chronos\Providers\ServiceProvider
        {
            public function register()
            {
                // If this registers it will define FooApp's
                // constructor parameter
                $this->app->defineParam('fooValue', 'bar');
            }
        };

        // Register the provider
        $app->register($provider);

        // Assert
        $this->assertSame('bar', ($app->make(AppFoo::class))->give());
    }
}