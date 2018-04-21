<?php

namespace Chronos\Services;

use Auryn\Injector;

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
     * @param Injector $apps
     * @param $id
     * @return Injector
     */
    abstract public function thread(Injector $apps, $id);
}