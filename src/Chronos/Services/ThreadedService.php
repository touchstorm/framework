<?php

namespace Chronos\Services;

use Chronos\Foundation\Application;
use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Chronos\Services\Exceptions\ThreadedServiceException;

abstract class ThreadedService
{
    /**
     * Application container
     * @var Application $app
     */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->bindQueueRepository();
    }

    /**
     * Threaded services require a running() method implementation
     * All dependency needed to execute a the thread dispatcher will be
     * bound in this method.
     */
    abstract public function running();

    /**
     * Threaded services require a thread() method implementation
     * All dependency needed to execute a thread will be
     * bound in this method.
     */
    abstract public function thread();

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

        $this->app = $this->app->alias(QueueRepositoryContract::class, $this->repository);
    }

    /**
     * @param $id
     * @throws \Auryn\ConfigException
     * @throws \Auryn\InjectionException
     */
    protected function bindThread($id)
    {
        // Resolve Queue repository
        $repository = $this->app->make(QueueRepositoryContract::class);

        // Fetch the Queue Item from the database (row of data to be processed)
        $queueItem = $repository->item($id);

        // Share the queue item to be used in the thread
        $this->app->share($queueItem);

        // Extract the class name
        $name = $this->parseClassName($queueItem->class);

        // Define a thread
        $thread = '\\App\\Console\\Controllers\\Threads\\' . $name;
        $this->app->define($thread, [
            ':queueItem' => $queueItem
        ]);

        // alias the polymorphic thread
        $this->app->alias('Thread', $thread);
    }

    /**
     * @param $ids
     * @throws \Auryn\ConfigException
     * @throws \Auryn\InjectionException
     */
    protected function bindThreadBatch(Array $ids = [])
    {
        // Resolve Queue repository
        $repository = $this->app->make(QueueRepositoryContract::class);

        // Fetch the Queue Item from the database (row of data to be processed)
        $queue = $repository->item($ids[0]);

        // Share the queue item to be used in the thread
        $this->app->share($batch);

        // Extract the class name
        $name = $this->parseClassName($queueItem->class);

        // Define a thread
        $thread = '\\App\\Console\\Controllers\\Threads\\' . $name;
        $this->app->define($thread, [
            ':queueItem' => $queueItem
        ]);

        // alias the polymorphic thread
        $this->app->alias('Batch', $thread);
    }

    /**
     * Loads the service providers
     * @param $method
     * @param null $id
     * @return mixed
     * @throws \Auryn\InjectionException
     * @throws \Auryn\ConfigException
     */
    public function register($method, $id = null)
    {
        if (!in_array($method, ['running', 'thread'])) {
            return $this->app;
        }

        // Bind service providers
        $this->bindProviders($method);

        // Make queue -> thread bindings
        if ($method == 'thread') {
            $this->bindThread($id);
        }

        // Call the specified method running | thread
        call_user_func([$this, $method], $id);

        // Return the container
        return $this->app;
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
            $this->app = $this->app->execute([$provider, 'registrar']);
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