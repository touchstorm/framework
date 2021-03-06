<?php

use Auryn\Injector;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\BatchKernel;
use PHPUnit\Framework\TestCase;

dirname(__FILE__)."/../../stubs/queues/MockBatchQueue.php";
dirname(__FILE__)."/../../stubs/repositories/MockBatchRepository.php";
dirname(__FILE__)."/../../stubs/services/MockBatchService.php";

class BatchesKernelTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\BatchKernel::handle
     */
    public function testRunningKernelConstruct()
    {
        $dir = dirname(__FILE__)."/../../stubs/";;
        // Set up the classes
        $app = new Application($dir);
        $app->register(MockServiceProvider::class);

        // Set variables
        $service = 'MockBatchService';

        $argv = [
            'batch.php',
            $service
        ];

        $namespace = $app->make(\Chronos\Helpers\NamespaceManager::class);

        $kernel = new BatchKernel($app, new ArgumentVectors($argv), $namespace);

        // Override options when handling
        $options = [
            'setDryRun' => true,
            'runUntilEmpty' => true,
            'setVerbose' => false,
            'fill' => function ($thread) {
                $thread->getRepository()->fill(new \Illuminate\Database\Eloquent\Collection(new MockBatchQueue()));
            }
        ];

        // Mock the kernel handling a call
        $kernel->handle($options);

        $this->assertSame($service, $kernel->getService());
        $this->assertInstanceOf(Injector::class, $kernel->getContainer());
    }
}