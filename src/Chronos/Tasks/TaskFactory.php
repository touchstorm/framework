<?php

namespace Chronos\Tasks;

/**
 * Class TaskFactory
 * @package Chronos\Application\Tasks
 */
class TaskFactory
{
    /**
     * Create a running task
     * @param $name
     * @param array $parameters
     * @return Running
     */
    public function running($name, $parameters = [])
    {
        if (is_array($parameters)) {
            $parameters = array_merge(['type' => 'running'], $parameters);
        }

        return new Running($name, $parameters);
    }

    /**
     * Created a scheduled task
     * Scheduled tasks are repetitive tasks
     * set to run at specified times:
     * - Yearly
     * - Monthly
     * - Daily
     * - Hourly
     * - By Minute
     * @param $name
     * @param array $options
     * @return Scheduled
     */
    public function scheduled($name, $options = [])
    {
        if (is_array($options)) {
            $options = array_merge(['type' => 'scheduled'], $options);
        }

        // When the task is scheduled to run
        $when = $options['at'];

        // Create task
        $task = new Scheduled($name, $options);

        // Run closure and return task
        return $when($task);
    }
}