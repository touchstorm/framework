<?php

namespace Chronos\Application\TaskMaster;

use Chronos\Application\TaskMaster\Contracts\TaskMasterContract;

/**
 * Class Watcher
 * @package Chronos\Application\TaskMaster
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
         * @var \Chronos\Application\Tasks\Running $task
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

            echo 'Initializing: ' . $task->getService() . PHP_EOL;
            $command = 'nohup php ' . getenv('APP_BASE') . '/running.php ' . $task->getService() . ' >/dev/null 2>&1 &';
            //exec($command);
            echo $command . PHP_EOL;
        }
    }

}