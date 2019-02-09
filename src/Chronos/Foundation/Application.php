<?php

namespace Chronos\Foundation;

use Auryn\Injector;
use Chronos\Controllers\Controller;
use Chronos\Providers\ServiceProvider;
use Chronos\Tasks\TaskCollector;
use Closure;

class Application extends Injector
{
    /**
     * Application version
     * @var string $version
     */
    protected $version = '2.0';

    // ?
    protected static $instance;

    /**
     * Base path of the running application
     * @var string
     */
    protected $basePath;

    /**
     * The service providers loaded in the system
     * @var array $loadedServiceProviders
     */
    protected $loadedServiceProviders = [];

    public function __construct($basePath = null)
    {
        parent::__construct();

        $this->setBasePath($basePath);

        // Define the various paths
        $this->definePaths();

        // Register the core application service providers
        $this->registerCoreProviders();

        // Register the tasks
        $this->registerTasks();
    }

    /**
     * When bootstrapping the Application
     * pass any specific global providers to be registered.
     * @param array $providers
     * @throws \Auryn\InjectionException
     */
    public function applicationProviders(array $providers = [])
    {
        foreach ($providers as $alias => $provider) {

            if (empty($provider)) {
                continue;
            }

            $this->register($provider);
        }
    }

    /**
     * Register Service Providers
     * Register and store the class name.
     * @param $provider
     * @param bool $reRegister
     * @return bool|mixed
     * @throws \Auryn\InjectionException
     */
    public function register($provider, $reRegister = false)
    {
        // If already registered return
        if (($loadedProvider = $this->getRegisteredProvider($provider)) && !$reRegister) {
            return $loadedProvider;
        }

        // If we're dealing with a string
        // resolve the class out of the IoC container
        if (is_string($provider)) {
            $provider = $this->make($provider);
        }

        // If registrar method exists on the class
        // pass it the container and register it
        if (method_exists($provider, 'registrar')) {
            $provider->registrar($this);
        }

        // Push our provider into the registered services array
        $this->pushRegisteredProvider(get_class($provider));

        return get_class($provider);
    }

    /**
     * Push registered provider into class array
     * @param $provider
     */
    protected function pushRegisteredProvider($provider)
    {
        $this->loadedServiceProviders[] = $provider;
    }

    /**
     * Get loaded service and resolve it out of the container
     * @param $provider
     * @return bool|mixed
     */
    public function getRegisteredProvider($provider)
    {
        if (is_object($provider) && $provider instanceOf ServiceProvider) {
            $provider = get_class($provider);
        }

        return in_array($provider, $this->loadedServiceProviders) ?
            $provider :
            false;
    }

    /**
     * Register all the needed service providers
     * @throws \Auryn\InjectionException
     */
    protected function registerCoreProviders()
    {
        $this->register(\Chronos\Providers\ArgumentVectorServiceProvider::class);
    }

    /**
     * Register tasks for dispatching
     * @throws \Auryn\ConfigException
     * @throws \Auryn\InjectionException
     */
    protected function registerTasks()
    {
        $task = $this->make(TaskCollector::class);

        $files = $this->getTaskFiles();

        foreach ($files as $file) {

            if (substr_compare($file, 'Tasks.php', -strlen('Tasks.php')) === 0) {

                require_once $this->getTaskFile($file);
            }
        }
        $this->share($task);
    }

    /**
     * Get the registered service providers
     * @return array
     */
    public function getRegisteredProviders()
    {
        return $this->loadedServiceProviders;
    }

    /**
     * Get all the defined tasks
     * @return array
     */
    protected function getTaskFiles()
    {
        return array_diff(scandir($this->getTasksPath()), ['..', '.']);
    }

    /**
     * Get a single task file
     * @param string $file
     * @return string|null
     */
    protected function getTaskFile($file = '')
    {
        return ($this->getTasksPath() . DIRECTORY_SEPARATOR . $file) ?: null;
    }

    /**
     * Define our internal paths
     */
    protected function definePaths()
    {
        $this->defineParam('basePath', $this->getBasePath());
        $this->defineParam('taskPath', $this->getTasksPath());
        $this->defineParam('testPath', $this->getTestPath());
        $this->defineParam('configPath', $this->getConfigPath());
    }

    /*
     * Set the base path
     */
    protected function setBasePath($path)
    {
        // Remove any trailing slashes
        // and add the only trailing slash needed
        // prevents a double // trailing slash
        $this->basePath = rtrim($path, '/') . DIRECTORY_SEPARATOR;
    }

    /**
     * Task path directory
     * @return string
     */
    public function getTasksPath()
    {
        return $this->getBasePath() . 'tasks' . DIRECTORY_SEPARATOR;
    }

    /**
     * Task path directory
     * @return string
     */
    public function getTestPath()
    {
        return $this->getBasePath() . 'test' . DIRECTORY_SEPARATOR;
    }

    /**
     * Task path directory
     * @return string
     */
    public function getConfigPath()
    {
        return $this->getBasePath() . 'config' . DIRECTORY_SEPARATOR;
    }

    /**
     * Application base path
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Resolve (hook make)
     * This wraps the make method and checks for
     * class types. Depending if it is a sub class
     * of a parent we can resolve service providers or
     * do other class specific work before we make()
     * and return.
     * @param $name
     * @param null $callback
     * @return mixed
     * @throws \Auryn\InjectionException
     */
    public function resolve($name, $callback = null)
    {
        // Depending on the class's extended parent
        // we can run some operations before we call
        // make and return an instance
        if (is_subclass_of($name, Controller::class)) {

            // Prepare our controller by resolving it's internal service providers
            // then resolve its booted providers
            $this->prepare($name, function ($controller, Application $app) {

                // Register the controller's providers
                foreach ($controller->providers ?? [] as $provider) {
                    $app->register($provider);
                }

                // Register the controller's booted providers
                foreach ($controller->booted ?? [] as $booted) {
                    $app->register($booted);
                }

            });
        }

        // Handle any custom closers
        if ($callback instanceof Closure) {
            $callback($name, $this);
        }

        return $name;
    }

    /**
     * Resolve and make the class from IoC
     * @param $name
     * @param array $args
     * @param null $callback
     * @return mixed
     * @throws \Auryn\InjectionException
     */
    public function resolveAndMake($name, array $args = [], $callback = null)
    {
        $name = $this->resolve($name, $callback);

        return $this->make($name, $args);
    }

    /**
     * Resolve providers and then execute
     * out of the IoC Depending on class type
     * @param $callableOrMethodStr
     * @param array $args
     * @param null $callback
     * @return mixed
     * @throws \Auryn\InjectionException
     */
    public function resolveAndExecute($callableOrMethodStr, array $args = [], $callback = null)
    {
        if (is_array($callableOrMethodStr)) {

            list($classOrObj, $method) = $callableOrMethodStr;

            $classOrObj = (is_object($classOrObj)) ?
                $this->resolve(get_class($classOrObj), $callback)
                :
                $this->resolve($classOrObj, $callback);

            $callableOrMethodStr = [
                $classOrObj,
                $method
            ];

        } elseif (is_object($callableOrMethodStr)) {
            $this->resolve(get_class($callableOrMethodStr), $callback);
        }

        return parent::execute($callableOrMethodStr, $args);
    }

}