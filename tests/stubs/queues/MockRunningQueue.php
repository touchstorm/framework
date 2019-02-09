<?php

use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\EloquentQueue;

class MockRunningQueue extends EloquentQueue implements QueueContract
{
    protected $connection = 'sqlite';

    public $class = MockThreadController::class;

    /**
     * Mock the find() needed to bring back a
     * queue
     * @param $id
     * @return $this
     */
    public function find($id)
    {
        return $this;
    }

    public function threadArguments()
    {
        return [
            $this->getAttribute('id')
        ];
    }
}