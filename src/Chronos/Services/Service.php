<?php

namespace Chronos\Services;

use Chronos\Foundation\Application;
use Chronos\Helpers\NamespaceManager;
use Chronos\Repositories\Contracts\QueueRepositoryContract;

abstract class Service
{
    /**
     * Application container
     * @var Application $app
     */
    protected $app;

    /**
     * @var string
     */
    protected $threadNamespace;

    /**
     * Service constructor.
     * @param Application $app
     * @param NamespaceManager $namespace
     * @throws \Auryn\ConfigException
     */
    public function __construct(Application $app, NamespaceManager $namespace)
    {
        $this->app = $app;
        $this->bindQueueRepository();
        $this->threadNamespace = $namespace->getThreadNamespace();
    }

    /**
     * Register the service providers & define thread params
     * @param $method
     */
    abstract public function register($method);

    /**
     * @return void
     * @throws \Auryn\ConfigException
     * @throws ThreadedServiceException
     */
    protected function bindQueueRepository()
    {
        if (!isset($this->repository)) {
            throw new ThreadedServiceException('Repository not found. Please add a repository attribute to your thread service.', 100);
        }

        $this->app->alias(QueueRepositoryContract::class, $this->repository);
    }

    /**
     * Bind method specific server providers
     * @param $method
     * @throws \Auryn\InjectionException
     */
    protected function bindProviders($method)
    {
        // Return, if there are no providers declared
        if (!isset($this->providers[$method])) {
            return;
        }

        // Load declared service providers
        foreach ($this->providers[$method] as $provider) {
            $this->app->register($provider);
        }
    }

    /**
     * @param $class
     * @return mixed
     */
    protected function parseClassName($class)
    {
        return str_replace('::class', '', $class);
    }

}