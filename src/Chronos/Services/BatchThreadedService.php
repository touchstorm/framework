<?php

namespace Chronos\Services;

use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Chronos\Services\Exceptions\ThreadedServiceException;

abstract class BatchThreadedService extends Service
{
    /**
     * Threaded services require a batch() method implementation
     * All dependency needed to execute a the thread dispatcher will be
     * bound in this method.
     */
    abstract public function batch();

    /**
     * Threaded services require a thread() method implementation
     * All dependency needed to execute a thread will be
     * bound in this method.
     */
    abstract public function thread();

    /**
     * @param $ids
     * @throws \Auryn\ConfigException
     * @throws \Auryn\InjectionException
     */
    protected function bindThreadBatch(string $ids = '')
    {
        // Resolve Queue repository
        $repository = $this->app->make(QueueRepositoryContract::class);

        $batch = $repository->items(explode('~', $ids));

        // Extract the class name
        $name = $this->parseClassName($repository->getClass());

        $thread = $this->threadNamespace . $name;

        $this->app->define($thread, [
            ':batch' => $batch
        ]);

        // alias the static thread
        $this->app->alias('Batch', $thread);
    }

    /**
     * Register the service providers & define thread params
     * @param $method
     * @param null $ids
     * @return mixed
     * @throws \Auryn\InjectionException
     * @throws \Auryn\ConfigException
     */
    public function register($method, $ids = null)
    {
        if (!in_array($method, ['batch', 'thread'])) {
            return $this->app;
        }

        // Bind service providers
        $this->bindProviders($method);

        // Make queue -> thread bindings
        if ($method == 'thread') {
            $this->bindThreadBatch($ids);
        }

        // Call the specified method running | thread
        call_user_func([$this, $method], $ids);

        // Return the container
        return $this->app;
    }

}