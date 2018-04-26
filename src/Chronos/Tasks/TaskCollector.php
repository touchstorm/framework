<?php

namespace Chronos\Tasks;

use Chronos\Tasks\Exceptions\TaskCollectionException;

/**
 * Class TaskCollector
 * @package Chronos\Tasks
 */
class TaskCollector
{
    /**
     * @var array $collection
     * A simple array collection of Tasks to be run.
     */
    protected $collection = [];

    /**
     * @var TaskFactory $taskFactory
     */
    protected $taskFactory;

    /**
     * TaskCollector constructor.
     * @param TaskFactory $taskFactory
     */
    public function __construct(TaskFactory $taskFactory)
    {
        $this->taskFactory = $taskFactory;
    }

    /**
     * Create and collect a new running route
     * @param $name
     * @param $options
     * @throws TaskCollectionException
     */
    public function running($name, $options)
    {
        // Server specific
        if (!$this->server($options)) {
            echo $name . ' is not designated to run on this server.' . PHP_EOL;
            return;
        }

        $task = $this->taskFactory->running($name, $options);

        $this->addTask($name, $task);
    }

    /**
     * Create and collect a new scheduled route
     * @param $name
     * @param $options
     * @return TaskCollector|void
     * @throws TaskCollectionException
     */
    public function scheduled($name, $options)
    {
        // Server specific
        if (!$this->server($options)) {
            echo $name . ' is not designated to run on this server.' . PHP_EOL;
            return;
        }

        // Assume all scheduled tasks run daily at midnight
        if (!isset($options['at'])) {
            $options['at'] = function ($task) {
                return $task->daily();
            };
        }

        $task = $this->taskFactory->scheduled($name, $options);

        $this->addTask($name, $task);

        return $this;
    }

    /**
     * Add a route
     * @param $name
     * @param Task $task
     * @throws TaskCollectionException
     */
    public function addTask($name, Task $task)
    {
        if (isset($this->conllection[$name])) {
            throw new TaskCollectionException('Task (' . $name . ') already exists');
        }

        $this->collection[$name] = $task;
    }

    /**
     * Get routes from the collector
     * @return Route[]
     */
    public function getTasks()
    {
        return $this->collection;
    }

    /**
     * Get a named task from the collector
     * @param mixed $task
     * @return Task
     */
    public function getTask($task)
    {
        if ($task instanceof Task) {
            return $this->collection[$task->getName()];
        }

        return $this->collection[$task];
    }

    /**
     * Server guard
     * - Allows the system to run tasks on
     * specific named servers.
     * @param array $option
     * @return bool
     */
    protected function server(Array $option = ['on' => null])
    {
        // If on option isn't set
        // then we default to run the task
        if (!isset($option['on'])) {
            return true;
        }

        // Detect if task is allowed on multiple servers
        if (is_array($option['on'])) {

            // Lowercase all server names
            $on = array_map(function ($item) {
                return strtolower($item);
            }, $option['on']);

            return in_array(getenv('APP_SERVER'), $on);
        }

        // Default Single server check
        return (getenv('APP_SERVER') == strtolower($option['on'])) ? true : false;
    }
}