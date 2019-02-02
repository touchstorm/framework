<?php

use Chronos\Foundation\Application;
use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\ScheduledKernel;
use PHPUnit\Framework\TestCase;


require_once dirname(__FILE__)."/../../stubs/providers/MockServiceProvider.php";
require_once dirname(__FILE__)."/../../stubs/controllers/MockController.php";

class ScheduledKernelFeatureTest extends TestCase
{
    /**
     *
     * @covers \Chronos\Kernel\ScheduledKernel::setNamespace
     * @covers \Chronos\Kernel\ScheduledKernel::handle
     * @covers \Chronos\Kernel\ScheduledKernel::getController
     * @covers \Chronos\Kernel\ScheduledKernel::getMethod
     * @covers \Chronos\Kernel\ScheduledKernel::getContainer
     * @covers \Chronos\Kernel\ScheduledKernel::getNamespace
     * @throws \Auryn\InjectionException
     */
    public function testKernelConstructAndServiceProviderResolution()
    {
        $dir = dirname(__FILE__)."/../../stubs/";;

        // Set up the classes
        $app = new Application($dir);
        $app->register(MockServiceProvider::class, true);

        // Set variables
        $namespace = '\\';
        $controller = 'MockController';
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

        // Assert
        $this->assertSame('bar', $output); // value being supplied by a getService provider defineParam
        $this->assertSame($controller, $kernel->getController());
        $this->assertSame($method, $kernel->getMethod());
        $this->assertSame($namespace, $kernel->getNamespace());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}