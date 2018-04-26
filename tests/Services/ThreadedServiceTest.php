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

class FooBarService extends ThreadedService
{
    public function running(Injector $app)
    {
        $app->alias(FooTest::class, BarTest::class);
        return $app;
    }

    public function thread(Injector $app, $id)
    {
        $app->alias(FooTest::class, BazTest::class);
        return $app;
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
        $service = new FooBarService();

        // Pass container into runng and bind FooTest contract to BarTest Concretion
        $container = $service->running(new Injector());
        $this->assertInstanceOf(BarTest::class, $container->make(FooTest::class));
        $this->assertSame('Bar', $container->execute([FooTest::class, 'hello']));

        // Pass container into thread and bind FooTest contract to BazTest Concretion
        $container = $service->thread($container, 1);
        $this->assertInstanceOf(BazTest::class, $container->make(FooTest::class));
        $this->assertSame('Baz', $container->execute([FooTest::class, 'hello']));
    }

}