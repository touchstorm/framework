<?php

use PHPUnit\Framework\TestCase;


class StubServiceProvider extends \Chronos\Providers\ServiceProvider
{
    public function register()
    {
        $this->app->defineParam('fooValue', 'bar');
    }
}

class StubController extends \Chronos\Controllers\Controller
{
    public $providers = [
        'StubServiceProvider'
    ];

    public function foo($fooValue)
    {
        return $fooValue;
    }
}

class ApplicationTest extends TestCase
{
    /**
     * @var string
     */
    protected $dir = '';

    /**
     * @var \Chronos\Foundation\Application
     */
    protected $app;

    public function setUp()
    {
        $this->dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'unit' . DIRECTORY_SEPARATOR . 'Foundation';
        $this->app = new \Chronos\Foundation\Application($this->dir);
    }

    /**
     * @covers \Chronos\Foundation\Application::getRegisteredProviders
     * @covers \Chronos\Foundation\Application::getRegisteredProvider
     */
    public function testRegisteredProviders()
    {
        $coreProvider = "Chronos\Providers\ArgumentVectorServiceProvider";

        $providers = $this->app->getRegisteredProviders();

        $this->assertNotEmpty($providers);
        $this->assertSame($coreProvider, $this->app->getRegisteredProvider($coreProvider));

    }

    /**
     * @covers \Chronos\Foundation\Application::basePath
     * @covers \Chronos\Foundation\Application::tasksPath
     * @covers \Chronos\Foundation\Application::testPath
     */
    public function testApplicationConstructAndPaths()
    {
        $this->assertSame($this->dir, $this->app->basePath());
        $this->assertSame($this->dir . DIRECTORY_SEPARATOR . 'tasks', $this->app->tasksPath());
        $this->assertSame($this->dir . DIRECTORY_SEPARATOR . 'test', $this->app->testPath());
    }

    public function testMakeMethodWithControllerHook()
    {
        $this->app->resolve(StubController::class);

        $value = $this->app->execute('StubController::foo');

        $this->assertSame(StubServiceProvider::class, $this->app->getRegisteredProvider(StubServiceProvider::class));
        $this->assertSame('bar', $value);

    }

}
