<?php

use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\EloquentQueue;

class MockBatchQueue extends EloquentQueue implements QueueContract
{
    protected $connection = 'sqlite';
}