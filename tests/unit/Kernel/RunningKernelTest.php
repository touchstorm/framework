<?php

use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\RunningKernel;
use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\Queue;
use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Chronos\Repositories\QueueRepository;
use Chronos\Services\ThreadedService;
use PHPUnit\Framework\TestCase;

class RunningTestQueue extends Queue implements QueueContract
{
    protected $connection = 'sqlite';
}

class RunningRepositoryUnitTest extends QueueRepository implements QueueRepositoryContract
{
    protected $connection = 'sqlite';
    protected $table = 'queue';
    protected $batchSize = 4;
    protected $maxThreads = 2;

    public function __construct(RunningTestQueue $queue)
    {
        parent::__construct($queue);
    }
}

class RunningServiceUnitTest extends ThreadedService
{
    protected $repository = RunningRepositoryUnitTest::class;

    public function running()
    {

    }

    public function thread()
    {

    }
}

class RunningKernelTest extends TestCase
{
    /**
     * @covers \Chronos\Kernel\RunningKernel::handle
     * @throws \Auryn\InjectionException
     */
    public function testRunningKernelConstruct()
    {
        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stubs';
        // Set up the classes
        $app = new \Chronos\Foundation\Application($dir);

        // Set variables
        $namespace = '\\';
        $service = 'RunningServiceUnitTest';

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
            'fill' => function($thread) {
               $thread->getRepository()->fill(new \Illuminate\Database\Eloquent\Collection(new RunningTestQueue()));
            }
        ];

        // Mock the kernel handling a call
        $kernel->handle(true, $options);

        $this->assertSame($service, $kernel->getService());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}