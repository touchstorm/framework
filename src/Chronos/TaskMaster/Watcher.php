<?php

namespace Chronos\TaskMaster;

use Chronos\TaskMaster\Contracts\TaskMasterContract;

/**
 * Class Watcher
 * @package Chronos\TaskMaster
 */
class Watcher extends BaseTaskMaster implements TaskMasterContract
{
    /**
     * Dispatch task to be processed
     */
    public function dispatch()
    {
        echo '////////////////////////////////////////////////////////////' . PHP_EOL;
        echo ' Watcher' . PHP_EOL;
        echo '////////////////////////////////////////////////////////////' . PHP_EOL;

        /**
         * @var string $name
         * @var \Chronos\Tasks\Running $task
         */
        foreach ($this->taskCollector->getRoutes() as $name => $task) {

            // Skip all but running tasks, skip
            if ($task->getType() != 'running') {
                continue;
            }

            // If task is already operating on the system, skip
            if ($this->isRunning($task->getService())) {
                continue;
            }

            // Create the command
            $command = 'nohup php ' . getenv('APP_BASE') . '/dispatch/running.php ' . $task->getService() . ' >/dev/null 2>&1 &';

            // Execute
            exec($command);

            // trigger taskStartEvent()
        }
    }

}