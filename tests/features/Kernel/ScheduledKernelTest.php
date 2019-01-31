<?php

use Chronos\Helpers\ArgumentVectors;
use Chronos\Kernel\ScheduledKernel;
use PHPUnit\Framework\TestCase;


class SomeFeatureProvider extends \Chronos\Providers\ServiceProvider
{
    public function register()
    {
        $controller = 'SomeFeatureController';
        $method = 'someMethod';
        $this->app->define(ArgumentVectors::class, [
            ':argv' => [
                'someDispatcher.php',
                $controller . '@' . $method
            ]
        ]);

        $this->app->defineParam('runFoo', 'bar');
    }
}

class SomeFeatureController extends \Chronos\Controllers\Controller
{
    public $providers = [
        SomeFeatureProvider::class
    ];

    public function someMethod($runFoo = 'baz')
    {
        return $runFoo;
    }

}

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
        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stubs';

        // Set up the classes
        $app = new \Chronos\Foundation\Application($dir);
        $app->register(SomeFeatureProvider::class, true);

        // Set variables
        $namespace = '\\';
        $controller = 'SomeFeatureController';
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
        $this->assertSame('bar', $output); // value being supplied by a service provider defineParam
        $this->assertSame($controller, $kernel->getController());
        $this->assertSame($method, $kernel->getMethod());
        $this->assertSame($namespace, $kernel->getNamespace());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}