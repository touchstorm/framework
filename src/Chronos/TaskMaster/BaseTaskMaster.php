<?php

namespace Chronos\TaskMaster;

use Chronos\Tasks\Task;
use Chronos\Tasks\TaskCollector;

/**
 * Class BaseTaskMaster
 * @package Chronos\TaskMaster
 */
class BaseTaskMaster
{
    /**
     * @var array $dispatched
     * - Container of tasks that have been dispatched
     */
    protected $dispatched = [];

    /**
     * @var array $dormant
     * - Container of tasks that remained dormant
     */
    protected $dormant = [];

    /**
     * @var array $outputs
     * - Container that store task log keyed
     * to the dispatched task's name.
     */
    protected $outputs = [];

    /**
     * @var RouteCollector $taskCollector
     * - Collection of defined tasks.
     */
    protected $taskCollector;

    /**
     * @var array $commands
     * - Array of commands that were dispatched.
     */
    protected $commands = [];

    /**
     * @var bool $verbose
     * - Allow the system to log
     */
    public $verbose = false;

    /**
     * BaseTaskMaster constructor.
     * @param TaskCollector $taskCollector
     */
    public function __construct(TaskCollector $taskCollector)
    {
        $this->taskCollector = $taskCollector;
    }

    /**
     * Check if task is running
     * @param $name
     * @return bool
     */
    public function isRunning($name)
    {
        $process = [];

        // Check the current processes that are running for the file
        $command = "ps aux | grep -i '" . $name . "' | grep -v grep | awk '{print $2}'";

        exec($command, $process);

        // Command not found running
        return !empty($process);
    }

    /**
     * Array of dispatched tasks
     * @return array
     */
    public function dispatchedTasks()
    {
        return $this->dispatched;
    }

    /**
     * Get specific dispatched task
     * @param Task $task
     * @return array
     */
    public function dispatchedTask(Task $task)
    {
        return $this->dispatched[$task->getName()] ?? [];
    }

    /**
     * Array of dormant tasks waiting to fire
     * @return array
     */
    public function dormantTasks()
    {
        return $this->dormant;
    }

    /**
     * Get a specific dormant task
     * @param Task $task
     * @return array
     */
    public function dormantTask(Task $task)
    {
        return $this->dormant[$task->getName()] ?? [];
    }

    /**
     * Get the log for a specific task
     * @return array
     */
    public function outputs()
    {
        return $this->outputs;
    }

    /**
     * Get the log for a specific task
     * @param Task $task
     * @return array|mixed
     */
    public function output(Task $task)
    {
        return $this->outputs[$task->getName()] ?? [];
    }

    /**
     * * Get tasks off the collector
     * @return \Chronos\Tasks\Route[]
     */
    public function getTasks()
    {
        return $this->taskCollector->getTasks();
    }

    /**
     * * Get tasks off the collector
     * @param Task $task
     * @return \Chronos\Tasks\Route[]
     */
    public function getTask(Task $task)
    {
        return $this->taskCollector->getTask($task);
    }

    /**
     * Output to the screen
     * @param $msg
     * @param bool $return
     */
    public function log($msg, $return = true)
    {
        if (!$this->verbose) {
            return;
        }

        echo $msg . (($return) ? "\n" : "\r");
    }
}