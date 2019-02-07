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

dirname(__FILE__) . "/../../stubs/controllers/MockController.php";

class ScheduledKernelTest extends TestCase
{
    public function testKernelConstruct()
    {
        $dir = dirname(__FILE__) . "/../../stubs/";;
        // Set up the classes
        $app = new \Chronos\Foundation\Application($dir);
        $app->register(MockServiceProvider::class);

        // Set variables
        $controller = 'MockController';
        $method = 'food';
        $argv = [
            'scheduled.php',
            $controller . '@' . $method
        ];

        $namespace = $app->make(\Chronos\Helpers\NamespaceManager::class);

        $kernel = new ScheduledKernel($app, new ArgumentVectors($argv), $namespace);

        // Mock the kernel handling a call
        $output = $kernel->handle(true);

        $this->assertNotNull($output);
        $this->assertSame($controller, $kernel->getController());
        $this->assertSame($method, $kernel->getMethod());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}