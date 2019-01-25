<?php

use Auryn\Injector;
use Chronos\Queues\Contracts\QueueContract;
use Chronos\Repositories\QueueRepository;
use Chronos\Services\ThreadedService;
use PHPUnit\Framework\TestCase;

interface FooTest
{
    function hello();
}

class BarTest implements FooTest
{
    function hello()
    {
        return 'Bar';
    }
}

class BazTest implements FooTest
{
    function hello()
    {
        return 'Baz';
    }
}

class FooQueue implements QueueContract
{

    public $class = 'SomeClass::class';

    function threadArguments()
    {
        return [$this->id];
    }

    function reschedule($date)
    {

    }

    function completed($reschedule, $date)
    {

    }

    function remove()
    {

    }

    function find($id)
    {
        return $this->id;
    }
}

class FooRepository extends QueueRepository
{
    public function __construct(FooQueue $queue)
    {
        $this->queue = $queue;
    }

    public function item($id)
    {
        return $this->queue;
    }


}

class FooBarService extends ThreadedService
{
    protected $repository = '\\FooRepository';

    protected $providers = [
        'running' => [],
        'thread' => []
    ];

    public function running()
    {
        $this->app->alias(FooTest::class, BarTest::class);
    }

    public function thread()
    {
        // One off binds
        $this->app->alias(FooTest::class, BazTest::class);
    }
}

class ThreadedServiceTest extends TestCase
{
    /**
     * @covers ThreadedService::running()
     * @covers ThreadedService::thread()
     */
    public function testThreadedServiceContainer()
    {
        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'features' . DIRECTORY_SEPARATOR . 'Foundation';
        // Pass container into running and bind FooTest contract to BarTest Concretion
        $container = (new FooBarService(new \Chronos\Foundation\Application($dir)))->register('running');
        $this->assertInstanceOf(BarTest::class, $container->make(FooTest::class));
        $this->assertSame('Bar', $container->execute([FooTest::class, 'hello']));

        // Pass container into thread and bind FooTest contract to BazTest Concretion
        /**
         * @var Injector $container
         */
        $container = (new FooBarService(new \Chronos\Foundation\Application($dir)))->register('thread', 1);
        $this->assertInstanceOf(BazTest::class, $container->make(FooTest::class));
        $this->assertSame('Baz', $container->execute([FooTest::class, 'hello']));
    }

}