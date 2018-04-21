<?php

namespace Chronos\Queues;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Queue
 * @package Chronos\Application\Queues
 */
class Queue extends Model
{
    /**
     * By default Chronos queues do not use
     * Eloquent's default timestamps
     * @var bool
     */
    public $timestamps = false;

    /**
     * Default fields required by all Queues
     * @var array
     */
    protected $fillable = [
        'in_use',
        'priority',
        'available_at'
    ];

    /**
     * Default vector arguments the Thread dispatcher
     * will use to pass to the individual Threads
     * @return array
     */
    public function threadArguments()
    {
        return [$this->getAttribute('id')];
    }
}