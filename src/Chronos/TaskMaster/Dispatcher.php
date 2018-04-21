<?php

namespace Chronos\TaskMaster;

use Chronos\TaskMaster\Contracts\TaskMasterContract;

/**
 * Class Dispatcher
 * @package Chronos\TaskMaster
 */
class Dispatcher extends BaseTaskMaster implements TaskMasterContract
{
    /**
     * Dispatch tasks to be processed
     */
    public function dispatch()
    {
        echo '////////////////////////////////////////////////////////////' . PHP_EOL;
        echo ' Scheduled ' . CURRENT_TIME . PHP_EOL;
        echo '////////////////////////////////////////////////////////////' . PHP_EOL;
        echo PHP_EOL;

        /**
         * @var string $name
         * @var \Chronos\Tasks\Scheduled $task
         */
        foreach ($this->taskCollector->getRoutes() as $name => $task) {

            // If not a scheduled task, skip
            if ($task->getType() != 'scheduled') {
                continue;
            }

            // If it is currently running or not available, skip
            if ($this->isRunning($task->getName()) || !$task->isAvailable()) {
                $this->dormant[] = $task;
                continue;
            }

            if ($command = $task->getCommand()) {
                $this->dispatched[] = $task;

                exec($command, $output);

                echo print_r($output, true) . PHP_EOL;
                continue;
            }

            $this->dispatched[] = $task;

            $command = 'nohup php ' . getenv('APP_BASE') . '/dispatch/scheduled.php ' . $task->getService() . ' >/dev/null 2>&1 &';
            exec($command);
            echo $command . PHP_EOL;
        }

        $this->dormant();

        $this->dispatched();
    }

    protected function dormant()
    {
        echo '------------------------------------------------------------' . PHP_EOL;
        echo ' DORMANT ' . CURRENT_TIME . PHP_EOL;
        echo '------------------------------------------------------------' . PHP_EOL;

        foreach ($this->dormant as $task) {
            echo ' > ' . $task . PHP_EOL;
        }

        echo PHP_EOL;
    }


    protected function dispatched()
    {
        echo '------------------------------------------------------------' . PHP_EOL;
        echo ' DISPATCHED ' . CURRENT_TIME . PHP_EOL;
        echo '------------------------------------------------------------' . PHP_EOL;

        foreach ($this->dispatched as $task) {
            echo ' > ' . $task . PHP_EOL;
        }

        echo PHP_EOL;

    }


}