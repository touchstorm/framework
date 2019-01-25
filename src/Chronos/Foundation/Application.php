<?php

namespace Chronos\Foundation;

use Auryn\Injector;
use Chronos\Providers\ServiceProvider;
use Chronos\Tasks\TaskCollector;

class Application extends Injector
{
    protected static $instance;

    protected $basePath = '';

    protected $loadedServiceProviders = [];

    public function __construct($basePath = null)
    {
        parent::__construct();

        $this->basePath = $basePath;

        // Register the core application service providers
        $this->registerCoreProviders();

        // Register the tasks
        $this->registerTasks();
    }

    /**
     * Register Service Providers
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
        return array_diff(scandir($this->tasksPath()), ['..', '.']);
    }

    /**
     * Get a single task file
     * @param string $file
     * @return null
     */
    protected function getTaskFile($file = '')
    {
        return ($this->tasksPath() . DIRECTORY_SEPARATOR . $file) ?: null;
    }

    /**
     * Task path directory
     * @return string
     */
    public function tasksPath()
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . 'tasks';
    }

    /**
     * Task path directory
     * @return string
     */
    public function testPath()
    {
        return $this->basePath() . DIRECTORY_SEPARATOR . 'test';
    }

    /**
     * Application base path
     * @return string
     */
    public function basePath()
    {
        return $this->basePath;
    }
}