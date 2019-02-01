<?php

use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\Queue;

class MockRunningQueue extends Queue implements QueueContract
{
    protected $connection = 'sqlite';
    public $class = 'FooClass::class';

    function threadArguments()
    {
        return [
            $this->getAttribute('id')
        ];
    }
}