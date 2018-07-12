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
     * @param Task $task
     * @return bool
     */
    public function isRunning(Task $task)
    {
        $process = $commands = [];

        // Check the current processes that are running for the file
        if ($name = $task->getName()) {
            $commands[] = "ps aux | grep -i '" . $task->getName() . "' | grep -v grep | awk '{print $2}'";
        }

        if ($service = $task->getService()) {
            $commands[] = "ps aux | grep -i '" . $task->getService() . "' | grep -v grep | awk '{print $2}'";
        }

        foreach ($commands as $command) {

            exec($command, $process);

            if (!empty($process)) {
                $this->log($process);
                return true;
            }
        }

        // Command not found running
        return false;
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
     * @param string $type
     * @return array
     */
    public function dispatchedTask(Task $task, $type = 'command')
    {
        foreach ($this->dispatched[$task->getName()] as $dispatchType => $dispatched) {

            if ($dispatchType == $type) {
                return $dispatched;
            }
        }

        return [];
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
     * @param string $type
     * @return array|mixed
     */
    public function output(Task $task, $type = 'command')
    {
        return $this->outputs[$task->getName()][$type] ?? [];
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
     * Collect a dormant task into its array container
     * @param Task $task
     */
    public function collectDormantTask(Task $task)
    {
        $this->dormant[$task->getName()] = $task;
    }

    /**
     * Collect a dispatched task into its array container
     * @param Task $task
     * @param string $type
     * @param string $command
     */
    public function collectDispatchedTask(Task $task, $type = 'command', string $command)
    {
        $this->dispatched[$task->getName()][$type][] = [
            'command' => $command,
            'task' => $task
        ];
    }

    /**
     * Collect a dormant task into its array container
     * @param Task $task
     * @param string $type
     * @param array $output
     */
    public function collectOutputs(Task $task, $type = 'command', Array $output)
    {
        $this->outputs[$task->getName()][$type][] = $output;
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

        if (is_array($msg)) {
            $msg = print_r($msg, true);
        }

        echo $msg . (($return) ? "\n" : "\r");
    }
}