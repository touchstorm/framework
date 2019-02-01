<?php

use Chronos\Foundation\Application;
use PHPUnit\Framework\TestCase;

require_once getcwd() . '/tests/stubs/repositories/MockRunningRepository.php';
require_once getcwd() . '/tests/stubs/services/MockRunningService.php';

class ApplicationTest extends TestCase
{
    /**
     * @var string
     */
    protected $dir = '';

    /**
     * @var Application
     */
    protected $app;

    public function setUp()
    {
        $this->dir = getcwd() . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'stubs';
        $this->app = new Application($this->dir);
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

    /**
     * @covers \Chronos\Foundation\Application::resolve
     * @covers \Chronos\Foundation\Application::execute
     * @throws \Auryn\InjectionException
     */
    public function testMakeMethodWithControllerHook()
    {
        $this->app->resolve(MockController::class);

        $value = $this->app->execute('MockController::foo');

        $this->assertSame(MockServiceProvider::class, $this->app->getRegisteredProvider(MockServiceProvider::class));
        $this->assertSame('bar', $value);
    }

    /**
     * @covers \Chronos\Foundation\Application::resolveAndMake
     * @throws \Auryn\InjectionException
     */
    public function testResolveAndMake()
    {
        $controller = $this->app->resolveAndMake(MockController::class);

        $value = $this->app->execute([$controller, 'foo']);

        $this->assertSame(MockServiceProvider::class, $this->app->getRegisteredProvider(MockServiceProvider::class));
        $this->assertSame('bar', $value);

    }

    /**
     * @covers \Chronos\Foundation\Application::resolveAndExecute
     * @throws \Auryn\InjectionException
     */
    public function testResolveAndExecuteFromArray()
    {
        $value = $this->app->resolveAndExecute([MockController::class, 'foo']);

        $this->assertSame(MockServiceProvider::class, $this->app->getRegisteredProvider(MockServiceProvider::class));
        $this->assertSame('bar', $value);

    }

    /**
     * @covers \Chronos\Foundation\Application::resolveAndExecute
     * @throws \Auryn\InjectionException
     */
    public function testResolveAndExecuteFromClass()
    {
        $controller = $this->app->make(MockController::class);

        $value = $this->app->resolveAndExecute([$controller, 'foo']);

        $this->assertSame(MockServiceProvider::class, $this->app->getRegisteredProvider(MockServiceProvider::class));
        $this->assertSame('bar', $value);

    }

}
