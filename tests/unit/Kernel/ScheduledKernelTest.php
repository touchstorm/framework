<?php

use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\ScheduledKernel;
use PHPUnit\Framework\TestCase;

class SomeUnitController
{
    public $providers = [];

    public function someMethod()
    {
        return 'hi';
    }
}

class ScheduledKernelTest extends TestCase
{
    public function testKernelConstruct()
    {
        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stubs';
        // Set up the classes
        $app = new \Chronos\Foundation\Application($dir);

        // Set variables
        $namespace = '\\';
        $controller = 'SomeUnitController';
        $method = 'someMethod';
        $argv = [
            'scheduled.php',
            $controller . '@' . $method
        ];

        $kernel = $app->make(ScheduledKernel::class, [
            ':app' => $app,
            ':arguments' => new ArgumentVectors($argv)
        ]);

        // Configure the kernel
        $kernel->setNamespace($namespace);

        // Mock the kernel handling a call
        $output = $kernel->handle(true);

        $this->assertNotNull($output);
        $this->assertSame($controller, $kernel->getController());
        $this->assertSame($method, $kernel->getMethod());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}