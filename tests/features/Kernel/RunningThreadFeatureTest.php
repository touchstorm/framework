<?php

use Auryn\Injector;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\RunningThreadKernel;
use PHPUnit\Framework\TestCase;


require_once dirname(__FILE__) . "/../../stubs/services/MockRunningService.php";
require_once dirname(__FILE__) . "/../../stubs/repositories/MockRunningRepository.php";
require_once dirname(__FILE__) . "/../../stubs/controllers/MockThreadController.php";

class RunningThreadFeatureTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\BatchThreadKernel::handle
     */
    public function testRunningKernelConstruct()
    {
        // Set up the classes
        $app = new Application(dirname(__FILE__) . "/../../stubs/");

        $provider = require_once dirname(__FILE__)."/../../stubs/providers/MockServiceProvider.php";
        $app->register($provider);

        // Set variables
        $service = 'MockRunningService';
        $queueId = 1;

        $argv = [
            'thread.php',
            $queueId,
            $service
        ];

        $kernel = $app->make(RunningThreadKernel::class, [
            ':app' => $app,
            ':arguments' => new ArgumentVectors($argv)
        ]);

        // Mock the kernel handling a call
        $kernel->handle(false);

        $this->assertSame($queueId, $kernel->getId());
        $this->assertSame($service, $kernel->getService());
        $this->assertInstanceOf(Injector::class, $kernel->getContainer());
    }
}