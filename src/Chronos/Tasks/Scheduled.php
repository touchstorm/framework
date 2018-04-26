<?php

namespace Chronos\Tasks;

use Cron\CronExpression;
use DateTime;

/**
 * Class Scheduled
 * @package Chronos\Tasks
 */
class Scheduled extends Task
{
    /**
     * When the task is scheduled to run
     * @var string $runs
     */
    public $runs = '* * * * *';

    /**
     * Positions translated into array keys
     * for convenient look up.
     * @var array $positions
     */
    protected $positions = [
        'minute' => 0,
        'hour' => 1,
        'day' => 2,
        'month' => 3,
        'dayOfWeek' => 4,
        'year' => 5
    ];

    /**
     * Day of week translated into valid
     * cron days
     * @var array $daysOfWeek
     */
    protected $daysOfWeek = [
        'monday' => 1,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6,
        'sunday' => 7,
        'mon' => 1,
        'tues' => 2,
        'wed' => 3,
        'thur' => 4,
        'fri' => 5,
        'sat' => 6,
        'sun' => 7,
    ];

    /**
     * isAvailable
     * - Is our scheduled cron available to run?
     * - CURRENT_TIME set while bootstrapping the IoC
     * @return bool
     */
    public function isAvailable()
    {
        return CronExpression::factory($this->runs)->isDue(CURRENT_TIME);
    }

    /**
     * Schedule
     * - Update the run time availability
     * @param $expression
     * @return Scheduled
     */
    protected function schedule($expression)
    {
        $this->runs = $expression;

        return $this;
    }

    /**
     * Monthly
     * - Default is January at midnight
     * @param int $month
     * @return Scheduled
     */
    public function monthly($month = 1)
    {
        return $this->schedule('0 0 * ' . $month . ' *');
    }

    /**
     * Days
     * - Specify which days you want to run
     * - Default is always midnight
     * @param array ...$days
     * @return Scheduled
     */
    public function days(...$days)
    {
        if (count($days) == 1) {
            return $this->updateRunTime('dayOfWeek', $days[0]);
        }

        // Translate any text days into int
        $days = array_map(function ($day) {

            if (is_string($day)) {
                return $this->daysOfWeek[$day] ?? null;
            }

            return $day;

        }, $days);

        $days = array_unique($days);

        return $this->updateRunTime('dayOfWeek', implode(',', $days));
    }

    /**
     * Hourly
     * - default midnight
     * @param int $hour 0 - 23
     * @return Scheduled
     */
    public function hourly($hour = 0)
    {
        return $this->schedule('* ' . $hour . ' * * *');
    }

    /**
     * Daily
     * - Default is midnight on monday
     * @param string * everyday
     * @return Scheduled
     */
    public function daily()
    {
        return $this->schedule('0 0 * * *');
    }

    /**
     * Weekly
     * - Default value is monday
     * @param mixed $day
     * @return Scheduled
     */
    public function weekly($day = 1)
    {
        if (is_string($day)) {

            // If day is a string range 0-7
            if (!isset($this->daysOfWeek[$day])) {
                return $this->updateRunTime('dayOfWeek', $day)->at('00:00');
            }

            // Date is a string like monday, mon, tuesday, tues etc
            return $this->updateRunTime('dayOfWeek', $this->daysOfWeek[$day])->at('00:00');
        }

        // Default integer
        return $this->updateRunTime('dayOfWeek', $day)->at('00:00');
    }

    /**
     * EveryMinute
     * - Always running every minute
     * - NOTE: System will not double run a cron
     * @return Scheduled
     */
    public function everyMinute()
    {
        return $this->schedule('* * * * *');
    }

    /**
     * Yearly
     * - Default Jan 1st at midnight
     * @param int $month 1 - 12
     * @param int $day
     * @return mixed
     */
    public function yearly($month = 1, $day = 1)
    {
        return $this->schedule('0 0 ' . $day . ' ' . $month . ' *');
    }

    /**
     * At
     * - Convert a time string into a cron expression
     * - Format H:i (00-23:00-59)
     * @param $time
     * @return Scheduled
     */
    public function at($time = '00:00')
    {
        $when = explode(':', $time);

        return $this->updateRunTime('hour', (int)$when[0])
            ->updateRunTime('minute', count($when) == 2 ? (int)$when[1] : '0');
    }

    /**
     * On
     * - Convert a valid DateTime string into a cron expression
     * @param $date
     * @return Scheduled
     */
    public function on($date)
    {
        $expression = (new DateTime($date))->format('i G j n *');

        // ! DateTime format does not support a single digit minute
        // Convert the minutes from 00 to 0
        // or 0# to #
        if (strstr($expression, '00')) {
            $expression = str_replace('00', '0', $expression);
        } else {
            $expression = ltrim($expression, 0);
        }

        return $this->schedule($expression);
    }

    /**
     * UpdateRunTime
     * - Updates the current position in the cron expression with appropriate value
     * @param string $when minute|hour|day|month|dayOfWeek|year
     * @param mixed $value
     * @return Scheduled
     */
    protected function updateRunTime(string $when, $value)
    {
        $runs = explode(' ', $this->runs);

        $runs[$this->positions[$when]] = $value;

        return $this->schedule(implode(' ', $runs));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() . ', Scheduled: ' . $this->runs . ' | Command: ' . $this->getCommand();
    }
}