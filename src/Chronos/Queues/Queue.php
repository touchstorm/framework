<?php

namespace Chronos\Queues;

use Carbon\Carbon;
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
        'available_at',
        'completed_at'
    ];

    /**
     * Default vector arguments the Thread dispatcher
     * will use to pass to the individual Threads
     * @return array
     */
    public function threadArguments()
    {
        return [
            'id' => $this->getAttribute('id')
        ];
    }

    public function getThreadArgument($key)
    {
        $argumentVectors = $this->threadArguments();

        return $argumentVectors[$key] ?? null;
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
            $date = new DateTime(((is_null($date)) ? '+24 hours' : $date));
        }

        // Update the queue
        $this->where('id', $this->getAttribute('id'))
            ->update([
                'available_at' => $date->format('Y-m-d H:i:s'),
                'in_use' => 0
            ]);
    }

    /**
     * Complete the queue item & reschedule (optional)
     * @param bool $reschedule
     * @param null $date
     */
    public function completed($reschedule = true, $date = null)
    {
        $this->where('id', $this->getAttribute('id'))
            ->update(['completed_at' => Carbon::now()]);

        if ($reschedule) {
            $this->reschedule($date);
        }
    }

    /**
     * Self delete from queue.
     */
    public function remove()
    {
        $this->delete();
    }

}