<?php

use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\BatchKernel;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

//dirname(__FILE__)."/../../stubs/queues/MockRunningQueue.php";
//dirname(__FILE__)."/../../stubs/repositories/MockRunningRepository.php";
//dirname(__FILE__)."/../../stubs/services/MockBatchService.php";

class BatchKernelFeatureTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\BatchKernel::handle
     * @covers \Chronos\Kernel\BatchKernel::setNamespace
     * @covers \Chronos\Kernel\BatchKernel::getService
     * @covers \Chronos\Kernel\BatchKernel::getContainer
     * @covers \Chronos\Kernel\BatchKernel::getNamespace
     * @throws \Auryn\InjectionException
     * @throws \Auryn\ConfigException
     */
    public function testBatchKernelConstruct()
    {
        // Set up the container
        $dir = dirname(__FILE__)."/../../stubs/";
        $app = new Application($dir);
        $app->share($app);

        // Set variables
        $namespace = '\\';
        $service = 'MockBatchService';
        $argv = [
            'batch.php',
            $service
        ];

        $app->share(new ArgumentVectors($argv));

        // This needs to resolve all dependencies from the IoC like
        // a live server would do
        $kernel = $app->make(BatchKernel::class);

        // Configure the kernel
        $kernel->setNamespace($namespace);

        $options = array(
            'setDryRun' => true,
            'runUntilEmpty' => true,
            'setVerbose' => false,
            'setCollectCommands' => true,
            'fill' => function ($thread) {
                $thread->getRepository()->fill(new Collection(new MockBatchQueue()));
            }
        );

        // Mock the kernel handling a call
        $kernel->handle($options);

        $this->assertSame($service, $kernel->getService());
        $this->assertSame($namespace, $kernel->getNamespace());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}