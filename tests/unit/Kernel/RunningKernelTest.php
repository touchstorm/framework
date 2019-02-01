<?php

use Auryn\Injector;
use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\RunningKernel;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

require_once getcwd() . '/tests/stubs/queues/MockRunningQueue.php';
require_once getcwd() . '/tests/stubs/repositories/MockRunningRepository.php';
require_once getcwd() . '/tests/stubs/services/MockRunningService.php';

class RunningKernelTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\RunningKernel::handle
     */
    public function testRunningKernelConstruct()
    {
        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stubs';
        // Set up the classes
        $app = new Application($dir);

        // Set variables
        $namespace = '\\';
        $service = 'MockRunningService';

        $argv = [
            'running.php',
            $service
        ];

        $kernel = new RunningKernel($app, new ArgumentVectors($argv));

        // Configure the kernel
        $kernel->setNamespace($namespace);

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