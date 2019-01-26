<?php

use Chronos\Kernel\ScheduledKernel;
use PHPUnit\Framework\TestCase;


class SomeFeatureProvider extends \Chronos\Providers\ServiceProvider
{
    public function register()
    {
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

    public function testKernelConstructAndServiceProviderResolution()
    {
        $dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'features' . DIRECTORY_SEPARATOR . 'Foundation';

        // Set up the classes
        $app = new \Chronos\Foundation\Application($dir);
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
        $this->assertSame('bar', $output); // value being supplied by a service provider defineParam
        $this->assertSame($controller, $kernel->getController());
        $this->assertSame($method, $kernel->getMethod());
        $this->assertInstanceOf(\Auryn\Injector::class, $kernel->getContainer());

    }
}