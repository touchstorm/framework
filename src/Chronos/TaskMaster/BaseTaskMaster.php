<?php

namespace Chronos\TaskMaster;

use Chronos\Tasks\TaskCollector;

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
     * @var RouteCollector
     */
    protected $taskCollector;

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
    protected function isRunning($name)
    {
        $process = [];

        // Check the current processes that are running for the file
        $command = "ps aux | grep -i '" . $name . "' | grep -v grep | awk '{print $2}'";

        exec($command, $process);

        // Command not found running
        return !empty($process);
    }
}