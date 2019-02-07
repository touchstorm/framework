<?php

use Auryn\Injector;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\RunningThreadKernel;
use PHPUnit\Framework\TestCase;


require_once dirname(__FILE__) . "/../../stubs/services/MockRunningService.php";
require_once dirname(__FILE__) . "/../../stubs/repositories/MockRunningRepository.php";
require_once dirname(__FILE__) . "/../../stubs/controllers/MockThreadController.php";

class RunningThreadKernelTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\BatchThreadKernel::handle
     */
    public function testRunningKernelConstruct()
    {
        // Set up the classes
        $app = new Application(dirname(__FILE__) . "/../../stubs/");
        $app->register(MockServiceProvider::class);

        // Set variables
        $service = 'MockRunningService';
        $queueId = 1;

        $argv = [
            'thread.php',
            $queueId,
            $service
        ];

        $namespace = $app->make(\Chronos\Helpers\NamespaceManager::class);

        $kernel = new RunningThreadKernel($app, new ArgumentVectors($argv), $namespace);

        // Mock the kernel handling a call
        $kernel->handle(false);

        $this->assertSame($queueId, $kernel->getId());
        $this->assertSame($service, $kernel->getService());
        $this->assertInstanceOf(Injector::class, $kernel->getContainer());
    }
}