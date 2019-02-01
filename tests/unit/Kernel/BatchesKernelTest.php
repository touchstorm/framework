<?php

use Auryn\Injector;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\BatchKernel;
use PHPUnit\Framework\TestCase;

require_once getcwd() . '/tests/stubs/queues/MockBatchQueue.php';
require_once getcwd() . '/tests/stubs/repositories/MockBatchRepository.php';
require_once getcwd() . '/tests/stubs/services/MockBatchService.php';

class BatchesKernelTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\BatchKernel::handle
     */
    public function testRunningKernelConstruct()
    {
        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stubs';
        // Set up the classes
        $app = new Application($dir);

        // Set variables
        $namespace = '\\';
        $service = 'MockBatchService';

        $argv = [
            'batch.php',
            $service
        ];

        $kernel = new BatchKernel($app, new ArgumentVectors($argv));

        // Configure the kernel
        $kernel->setNamespace($namespace);

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