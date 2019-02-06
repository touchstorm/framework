<?php

namespace Chronos\TaskMaster;

use Chronos\TaskMaster\Contracts\TaskMasterContract;
use Chronos\Tasks\Task;
use LucidFrame\Console\ConsoleTable;

/**
 * Class Watcher
 * @package Chronos\TaskMaster
 */
class Destroyer extends BaseTaskMaster implements TaskMasterContract
{
    /**
     * Dispatch task to be processed
     * @param array $options
     */
    public function dispatch($options = [])
    {
        // Pass through optional inputs for configuration
        $this->configure($options);

        $this->log('////////////////////////////////////////////////////////////');
        $this->log(' Destroyer ' . CURRENT_TIME);
        $this->log('////////////////////////////////////////////////////////////');

        /**
         * @var string $name
         * @var \Chronos\Tasks\Running $task
         */
        foreach ($this->taskCollector->getTasks() as $name => $task) {

            // If task is already operating on the system, skip
            if (!$processId = $this->isRunning($task)) {
                continue;
            }

            // Execute the task's main command
            $this->execute(['kill -9 ' . $processId], $task, 'kill');
        }
    }

}