<?php

use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\Queue;

class MockBatchQueue extends Queue implements QueueContract
{
    protected $connection = 'sqlite';
}