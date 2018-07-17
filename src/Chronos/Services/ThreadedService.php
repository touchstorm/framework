<?php

namespace Chronos\Services;

use Auryn\Injector;
use Chronos\Repositories\Contracts\QueueRepositoryContract;

abstract class ThreadedService
{
    /**
     * Threaded services require a running() method implementation
     * All dependency needed to execute a the thread dispatcher will be
     * bound in this method.
     * @param Injector $app
     * @return Injector
     */
    abstract public function running(Injector $app);

    /**
     * Threaded services require a thread() method implementation
     * All dependency needed to execute a thread will be
     * bound in this method.
     * @param Injector $app
     * @param $id
     * @return Injector
     */
    abstract public function thread(Injector $app, $id);

    /**
     * @param Injector $app
     * @return Injector
     * @throws ConfigException
     * @throws \Auryn\ConfigException
     */
    protected function bindQueueRepository(Injector $app)
    {
        return $app->alias(QueueRepositoryContract::class, $this->repository);
    }

}