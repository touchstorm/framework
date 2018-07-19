<?php

namespace Chronos\Queues;

use DateTime;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Queue
 * @package Chronos\Queues
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

    /**
     * Reschedule the queue item for its next run
     * default +24 hours from now.
     * @param $date
     */
    public function reschedule($date = null)
    {
        // Default is always +24 hours
        if (!$date instanceof DateTime) {
            $date = new DateTime('+24 hours');
        }

        // Update the queue
        $this->where('id', $this->getAttribute('id'))
            ->update([
                'available_at' => (new DateTime($date))->format('Y-m-d H:i:s'),
                'in_use' => 0
            ]);
    }

    /**
     * Self delete from queue.
     */
    public function remove()
    {
        $this->delete();
    }

}