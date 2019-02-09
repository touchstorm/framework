<?php

namespace Chronos\Services;

use Chronos\Repositories\Contracts\QueueRepositoryContract;

abstract class ThreadedService extends Service
{
    /**
     * Threaded services require a running() method implementation
     * All dependency needed to execute a the thread dispatcher will be
     * bound in this getMethod.
     */
    abstract public function running();

    /**
     * Threaded services require a thread() method implementation
     * All dependency needed to execute a thread will be
     * bound in this getMethod.
     */
    abstract public function thread();

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
        $thread = $this->threadNamespace . $name;
        $this->app->define($thread, [
            ':queueItem' => $queueItem
        ]);

        // alias the polymorphic thread
        $this->app->alias('Thread', $thread);
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

        // Bind getService providers
        $this->bindProviders($method);

        // Make queue -> thread bindings
        if ($method == 'thread') {
            $this->bindThread($id);
        }

        // Call the specified getMethod running | thread
        call_user_func([$this, $method], $id);

        // Return the container
        return $this->app;
    }
}