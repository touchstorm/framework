<?php

use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\RunningKernel;
use PHPUnit\Framework\TestCase;

require_once getcwd() . '/tests/stubs/queues/MockRunningQueue.php';
require_once getcwd() . '/tests/stubs/repositories/MockRunningRepository.php';
require_once getcwd() . '/tests/stubs/services/MockBatchService.php';

class BatchKernelFeatureTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\RunningKernel::handle
     * @throws \Auryn\InjectionException
     */
    public function testBatchKernelConstruct()
    {
        // create a service
        // create a repository

        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stubs';
        // Set up the classes
        $app = new \Chronos\Foundation\Application($dir);

        // Set variables
        $namespace = '\\';
        $service = 'MockBatchService';

        $argv = [
            'running.php',
            $service
        ];

        $kernel = $app->make(RunningKernel::class, [
            ':app' => $app,
            ':arguments' => new ArgumentVectors($argv)
        ]);

        // Configure the kernel
        $kernel->setNamespace($namespace);

        $options = [
            'setDryRun' => true,
            'runUntilEmpty' => true,
            'setVerbose' => false,
            'fill' => function ($thread) {
                $thread->getRepository()->fill(new \Illuminate\Database\Eloquent\Collection(new MockBatchQueue()));
            }
        ];

        // Mock the kernel handling a call
        $kernel->handle(true, $options);

        $this->assertSame($service, $kernel->getService());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}