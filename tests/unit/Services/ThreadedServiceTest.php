<?php

use Auryn\Injector;
use Chronos\Services\ThreadedService;
use PHPUnit\Framework\TestCase;


require_once dirname(__FILE__)."/../../stubs/MockContract.php";
require_once dirname(__FILE__)."/../../stubs/MockClass.php";
require_once dirname(__FILE__)."/../../stubs/services/MockRunningService.php";

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
        $dir = dirname(__FILE__)."/../../stubs/";

        $namespace = '\\';

        // Pass container into running and bind FooTest contract to BarTest Concretion
        $container = (new MockRunningService(new \Chronos\Foundation\Application($dir), $namespace))->register('running');
        $this->assertInstanceOf(MockClass::class, $container->make(MockContract::class));
        $this->assertSame('found', $container->execute([MockContract::class, 'find']));

        // Pass container into thread and bind FooTest contract to BazTest Concretion
        /**
         * @var Injector $container
         */
        $service = new MockRunningService(new \Chronos\Foundation\Application($dir), $namespace);

        $container = $service->register('thread', 1);
        $this->assertInstanceOf(MockClass::class, $container->make(MockContract::class));
        $this->assertSame('found', $container->execute([MockContract::class, 'find']));
    }

}