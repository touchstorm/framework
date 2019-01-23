<?php

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
        // Set up the classes
        $app = new \Auryn\Injector();
        $kernel = new ScheduledKernel($app);

        // Set variables
        $namespace = '\\';
        $controller = 'SomeUnitController';
        $method = 'someMethod';
        $argv = [
            'someDispatcher.php',
            $controller . '@' . $method
        ];

        // Configure the kernel
        $kernel->setNamespace($namespace);

        // Mock the kernel handling a call
        $kernel->handle($argv, true);

        $this->assertSame($controller, $kernel->getController());
        $this->assertSame($method, $kernel->getMethod());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}