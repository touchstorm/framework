<?php

use Auryn\Injector;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\BatchThreadKernel;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;


require_once dirname(__FILE__) . "/../../stubs/services/MockBatchService.php";
require_once dirname(__FILE__) . "/../../stubs/repositories/MockBatchRepository.php";
require_once dirname(__FILE__) . "/../../stubs/controllers/MockBatchThreadController.php";

class BatchThreadKernelTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\BatchThreadKernel::handle
     */
    public function testRunningKernelConstruct()
    {
        // Set up the classes
        $app = new Application(dirname(__FILE__)."/../../stubs/");

        // Set variables
        $namespace = '\\';
        $service = 'MockBatchService';
        $batchQueueIds = '1~2~3~4';

        $argv = [
            'batchThread.php',
            $batchQueueIds,
            $service
        ];

        $app->defineParam('namespace', $namespace);

        $kernel = new BatchThreadKernel($app, new ArgumentVectors($argv));

        // Configure the kernel
        $kernel->setNamespace($namespace);

        // Mock the kernel handling a call
        $kernel->handle(false);

        $this->assertSame($batchQueueIds, $kernel->getIds());
        $this->assertSame($service, $kernel->getService());
        $this->assertInstanceOf(Injector::class, $kernel->getContainer());
    }
}