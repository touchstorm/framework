<?php

use Auryn\Injector;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\BatchThreadKernel;
use PHPUnit\Framework\TestCase;


require_once dirname(__FILE__) . "/../../stubs/services/MockBatchService.php";
require_once dirname(__FILE__) . "/../../stubs/repositories/MockBatchRepository.php";
require_once dirname(__FILE__) . "/../../stubs/controllers/MockBatchThreadController.php";

class BatchThreadKernelTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\BatchThreadKernel::handle
     */
    public function testBatchThreadKernelConstruct()
    {
        // Set up the classes
        $app = new Application(dirname(__FILE__)."/../../stubs/");
        $app->register(MockServiceProvider::class, true);

        // Set variables
        $service = 'MockBatchService';
        $batchQueueIds = '1~2~3~4';

        $argv = [
            'batchThread.php',
            $batchQueueIds,
            $service
        ];

        $namespace = $app->make(\Chronos\Helpers\NamespaceManager::class);

        $kernel = new BatchThreadKernel($app, new ArgumentVectors($argv), $namespace);

        // Mock the kernel handling a call
        $kernel->handle(false);

        $this->assertSame($batchQueueIds, $kernel->getIds());
        $this->assertSame($service, $kernel->getService());
        $this->assertInstanceOf(Injector::class, $kernel->getContainer());
    }
}