<?php

namespace Chronos\Tasks;

/**
 * Class TaskFactory
 * @package Chronos\Tasks
 */
class TaskFactory
{
    /**
     * Create a running task
     * @param $name
     * @param array $parameters
     * @return Running
     * @throws Exceptions\TaskCollectionException
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
     * - Weekly
     * - By Minute
     * - By Date (one off)
     * @param $name
     * @param array $options
     * @return Scheduled
     */
    public function scheduled($name, $options = [])
    {
        // Set options
        if (is_array($options)) {
            $options = array_merge(['type' => 'scheduled'], $options);
        }

        // Controller commands
        if (!isset($options['command']) && isset($options['uses'])) {
            $options['controlCommand'] = 'php ' . getenv('APP_BASE') . '/dispatch/scheduled.php ' . $options['uses'];
        }

        if (isset($options['command']) && !isset($options['uses'])) {
            // This is a one-off command
            $options['async'] = (isset($options['async'])) ? $options['async'] : false;
        }

        if (!isset($options['command']) && !isset($options['uses'])) {
            $options['command'] = 'echo Wyld Stallyn Rules!';
        }

        // When the task is scheduled to run
        $when = $options['at'];

        // Create task
        $task = new Scheduled($name, $options);

        // Run closure and return task
        return $when($task);
    }
}