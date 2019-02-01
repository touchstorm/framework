<?php

use Auryn\Injector;
use Chronos\Services\ThreadedService;
use PHPUnit\Framework\TestCase;


require_once getcwd() . '/tests/stubs/MockContract.php';
require_once getcwd() . '/tests/stubs/MockClass.php';
require_once getcwd() . '/tests/stubs/services/MockRunningService.php';

class ThreadedServiceTest extends TestCase
{
    /**
     * @covers ThreadedService::running()
     * @covers ThreadedService::thread()
     * @covers ThreadedService::bindQueueRepository()
     * @covers ThreadedService::bindProviders()
     * @covers ThreadedService::bindThread()
     * @covers ThreadedService::parseClassName()
     */
    public function testThreadedServiceContainer()
    {
        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stubs';

        // Pass container into running and bind FooTest contract to BarTest Concretion
        $container = (new MockRunningService(new \Chronos\Foundation\Application($dir)))->register('running');
        $this->assertInstanceOf(MockClass::class, $container->make(MockContract::class));
        $this->assertSame('found', $container->execute([MockContract::class, 'find']));

        // Pass container into thread and bind FooTest contract to BazTest Concretion
        /**
         * @var Injector $container
         */
        $service = new MockRunningService(new \Chronos\Foundation\Application($dir));

        $container = $service->register('thread', 1);
        $this->assertInstanceOf(MockClass::class, $container->make(MockContract::class));
        $this->assertSame('found', $container->execute([MockContract::class, 'find']));
    }

}