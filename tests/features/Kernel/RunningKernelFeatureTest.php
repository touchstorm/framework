<?php

use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\RunningKernel;
use PHPUnit\Framework\TestCase;

dirname(__FILE__)."/../../stubs/queues/MockRunningQueue.php";
dirname(__FILE__)."/../../stubs/repositories/MockRunningRepository.php";
dirname(__FILE__)."/../../stubs/services/MockRunningService.php";

class RunningKernelFeatureTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\RunningKernel::handle
     * @throws \Auryn\InjectionException
     */
    public function testRunningKernelConstruct()
    {

        $dir = dirname(__FILE__)."/../../stubs/";;
        // Set up the classes
        $app = new Application($dir);

        $provider = require_once dirname(__FILE__)."/../../stubs/providers/MockServiceProvider.php";
        $app->register($provider);

        // Set variables
        $service = 'MockRunningService';

        $argv = [
            'running.php',
            $service
        ];

        $kernel = $app->make(RunningKernel::class, [
            ':app' => $app,
            ':arguments' => new ArgumentVectors($argv)
        ]);


        $options = [
            'setDryRun' => true,
            'runUntilEmpty' => true,
            'setVerbose' => false,
            'fill' => function ($thread) {
                $thread->getRepository()->fill(new \Illuminate\Database\Eloquent\Collection(new MockRunningQueue()));
            }
        ];

        // Mock the kernel handling a call
        $kernel->handle(true, $options);

        $this->assertSame($service, $kernel->getService());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}