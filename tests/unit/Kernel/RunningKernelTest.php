<?php

use Auryn\Injector;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\RunningKernel;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

dirname(__FILE__)."/../../stubs/queues/MockRunningQueue.php";
dirname(__FILE__)."/../../stubs/repositories/MockRunningRepository.php";
dirname(__FILE__)."/../../stubs/services/MockRunningService.php";

class RunningKernelTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\RunningKernel::handle
     */
    public function testRunningKernelConstruct()
    {
        $dir = dirname(__FILE__)."/../../stubs/";;
        // Set up the classes
        $app = new Application($dir);

        // Set variables
        $service = 'MockRunningService';

        $argv = [
            'running.php',
            $service
        ];

        $namespace = $app->make(\Chronos\Helpers\NamespaceManager::class);

        $kernel = new RunningKernel($app, new ArgumentVectors($argv), $namespace);


        // Override options when handling
        $options = array(
            'setDryRun' => true,
            'runUntilEmpty' => true,
            'setVerbose' => false,
            'fill' => function ($thread) {
                $thread->getRepository()->fill(new Collection(new MockRunningQueue()));
            }
        );

        // Mock the kernel handling a call
        $kernel->handle(true, $options);

        $this->assertSame($service, $kernel->getService());
        $this->assertInstanceOf(Injector::class, $kernel->getContainer());
        $this->assertInstanceOf(Application::class, $kernel->getContainer());

    }
}