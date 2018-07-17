<?php

namespace Chronos\Services;

use Auryn\Injector;
use Chronos\Repositories\Contracts\QueueRepositoryContract;

abstract class ThreadedService
{
    /**
     * Application container
     * @var Injector $app
     */
    protected $app;

    public function __construct(Injector $app)
    {
        $this->app = $app;
        $this->bindQueueRepository();
    }

    /**
     * Threaded services require a running() method implementation
     * All dependency needed to execute a the thread dispatcher will be
     * bound in this method.
     * @return Injector
     */
    abstract public function running();

    /**
     * Threaded services require a thread() method implementation
     * All dependency needed to execute a thread will be
     * bound in this method.
     * @param $id
     * @return Injector
     */
    abstract public function thread($id);

    /**
     * @return void
     * @throws \Auryn\ConfigException
     */
    protected function bindQueueRepository()
    {
        $this->app = $this->app->alias(QueueRepositoryContract::class, $this->repository);
    }

    /**
     * Loads the service providers
     * @param $method
     * @param null $arguments
     * @return mixed
     * @throws \Auryn\InjectionException
     */
    public function dispatch($method, $arguments = null)
    {
        if (!in_array($method, ['running', 'thread'])) {
            return $this->app;
        }

        // Load any service providers
        foreach ($this->providers[$method] as $provider) {
            $this->app = $this->app->execute([$provider, 'register'], [':app', $this->app]);
        }

        // Call the specified method running | thread
        call_user_func([$this, $method], $arguments);

        // Return the container
        return $this->app;
    }
}