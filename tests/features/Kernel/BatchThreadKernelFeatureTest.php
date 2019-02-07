<?php

use Auryn\Injector;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\BatchThreadKernel;
use PHPUnit\Framework\TestCase;

require_once dirname(__FILE__) . "/../../stubs/providers/MockServiceProvider.php";
require_once dirname(__FILE__) . "/../../stubs/services/MockBatchService.php";
require_once dirname(__FILE__) . "/../../stubs/queues/MockBatchQueue.php";
require_once dirname(__FILE__) . "/../../stubs/repositories/MockBatchRepository.php";
require_once dirname(__FILE__) . "/../../stubs/controllers/MockBatchThreadController.php";

class BatchThreadKernelFeatureTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\BatchThreadKernel::handle
     */
    public function testRunningKernelConstruct()
    {
        $dir = dirname(__FILE__) . "/../../stubs/";;
        // Set up the classes
        $app = new Application($dir);

        $provider = require_once dirname(__FILE__)."/../../stubs/providers/MockServiceProvider.php";
        $app->register($provider);

        /// Set variables
        $namespace = '\\';
        $service = 'MockBatchService';
        $batchQueueIds = '1~2~3~4';

        $argv = [
            'batchThread.php',
            $batchQueueIds,
            $service
        ];

        $kernel = $app->make(BatchThreadKernel::class, [
            ':app' => $app,
            ':arguments' => new ArgumentVectors($argv)
        ]);

        $app->defineParam('namespace', $namespace);

        // Mock the kernel handling a call
        $kernel->handle(false);

        $this->assertSame($batchQueueIds, $kernel->getIds());
        $this->assertSame($service, $kernel->getService());
        $this->assertInstanceOf(Injector::class, $kernel->getContainer());
    }
}