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
     * @var array $running
     * - Container of tasks that are currently running
     */
    protected $running = [];

    /**
     * @var array $batch
     * - Container of tasks that are currently running
     */
    protected $batch = [];

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
     * @var bool $dryRun
     * - Allow the system to dry run
     * without really launching a nohup task
     */
    protected $dryRun = false;

    /**
     * BaseTaskMaster constructor.
     * @param TaskCollector $taskCollector
     */
    public function __construct(TaskCollector $taskCollector)
    {
        $this->taskCollector = $taskCollector;
    }

    /**
     * Detect which type of command
     * - bash/command line
     * - controller@method
     * and execute
     * @param $commands
     * @param Task $task
     * @param string $type
     */
    protected function execute($commands, Task $task, $type = 'command')
    {
        if (!is_array($commands)) {
            return;
        }

        $descriptors = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "r"]
        ];

        foreach ($commands as $command) {

            $this->collectDispatchedTask($task, $type, $command);

            if ($this->dryRun) {
                continue;
            }

            $output = [];
            //exec($command, $output);

            $process = proc_open($command, $descriptors, $pipes);

            $stout = stream_get_contents($pipes[1]);

            fclose($pipes[1]);

            proc_close($process);

            if (!empty($stout)) {
                $output[] = trim($stout);
            }

            $output = !empty($output) ? $output : ['asynchronous'];

            $this->collectOutputs($task, $type, $output);
        }

        return;
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
                return (int)$process[0];
            }
        }

        // Command not found running
        return 0;
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
     * @param string $type command|before|after|running|batch
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
     * Array of running tasks
     * @return array
     */
    public function runningTasks()
    {
        return $this->running;
    }

    /**
     * Array of running batch tasks
     * @return array
     */
    public function batchTasks()
    {
        return $this->batch;
    }

    /**
     * Get specific dispatched task
     * @param Task $task
     * @param string $type
     * @return array
     */
    public function runningTask(Task $task, $type = 'command')
    {
        foreach ($this->dispatched[$task->getName()] as $running) {
            return $running;
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
     * @param $task
     * @return \Chronos\Tasks\Route[]
     */
    public function getTask($task)
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
     * Collect a running tasks into its array container
     * @param Task $task
     */
    public function collectRunningTask(Task $task)
    {
        $this->running[$task->getName()] = $task;
    }

    /**
     * Collect a running batch tasks into its array container
     * @param Task $task
     */
    public function collectBatchTask(Task $task)
    {
        $this->batch[$task->getName()] = $task;
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
     * Get output status
     * @return bool
     */
    public function getVerbose()
    {
        return $this->verbose;
    }

    /**
     * Set output status
     * @param bool $value
     */
    public function setVerbose($value = false)
    {
        $this->verbose = $value;
    }

    /**
     * Get dry run value
     * @return bool
     */
    public function getDryRun()
    {
        return $this->dryRun;
    }

    /**
     * Set output status
     * @param bool $value
     */
    public function setDryRun($value = false)
    {
        $this->dryRun = $value;
    }

    /**
     * @param array $options
     */
    protected function configure(array $options)
    {
        if (!isset($options) || empty($options)) {
            return;
        }

        foreach ($options as $setter => $value) {

            // Parse closure settings and continue
            if ($value instanceof Closure) {
                $value($this);
                continue;
            }

            // if it is a setter method set and continue
            if (method_exists($this, $setter)) {
                call_user_func([$this, $setter], $value);
                continue;
            }
        }

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