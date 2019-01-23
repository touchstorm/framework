<?php

use Chronos\Kernel\ScheduledKernel;
use PHPUnit\Framework\TestCase;


class SomeFeatureController
{
    public $providers = [];

    public function someMethod()
    {
        return 'hi';
    }
}

class ScheduledKernelFeatureTest extends TestCase
{
    public function testKernelConstruct()
    {
        // Set up the classes
        $app = new \Auryn\Injector();
        $kernel = new ScheduledKernel($app);

        // Set variables
        $namespace = '\\';
        $controller = 'SomeFeatureController';
        $method = 'someMethod';
        $argv = [
            'someDispatcher.php',
            $controller . '@' . $method
        ];

        // Configure the kernel
        $kernel->setNamespace($namespace);

        // Mock the kernel handling a call
        $output = $kernel->handle($argv, true);

        // Assert
        $this->assertSame('hi', $output);
        $this->assertSame($controller, $kernel->getController());
        $this->assertSame($method, $kernel->getMethod());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}