<?php

use Auryn\Injector;
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

class FooRepository
{

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

    public function thread($id)
    {
        $this->app->defineParam('argument', $id);
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

        // Pass container into running and bind FooTest contract to BarTest Concretion
        $container = (new FooBarService(new Injector()))->dispatch('running');
        $this->assertInstanceOf(BarTest::class, $container->make(FooTest::class));
        $this->assertSame('Bar', $container->execute([FooTest::class, 'hello']));

        // Pass container into thread and bind FooTest contract to BazTest Concretion
        /**
         * @var Injector $container
         */
        $container = (new FooBarService(new Injector()))->dispatch('thread', 1);
        $this->assertInstanceOf(BazTest::class, $container->make(FooTest::class));
        $this->assertSame('Baz', $container->execute([FooTest::class, 'hello']));
    }

}