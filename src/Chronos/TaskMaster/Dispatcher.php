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
        $this->log('////////////////////////////////////////////////////////////');
        $this->log(' Scheduled ' . CURRENT_TIME);
        $this->log('////////////////////////////////////////////////////////////');
        $this->log('');

        /**
         * @var string $name
         * @var \Chronos\Tasks\Scheduled $task
         */
        foreach ($this->taskCollector->getTasks() as $name => $task) {

            // If not a scheduled task, skip
            if ($task->getType() != 'scheduled') {
                continue;
            }

            // If it is currently running or not available, skip
            if ($this->isRunning($task->getName()) || !$task->isAvailable()) {
                $this->dormant[$task->getName()] = $task;
                continue;
            }

            // If there is a one-off command fire and continue
            if ($command = $task->getCommand()) {
                $this->dispatched[$task->getName()] = $task;

                // Launch command
                exec($command, $output);

                // Add log to outputs
                $this->outputs[$task->getName()] = $output;

                $output = [];

                continue;
            }

            // Dispatched container
            $this->dispatched[] = $task;

            // Set up the command
            $command = 'nohup php ' . getenv('APP_BASE') . '/dispatch/scheduled.php ' . $task->getService() . ' >/dev/null 2>&1 &';

            // Fire
            exec($command);

            // trigger taskStartEvent()
        }

        // Output reports
        $this->dormant();
        $this->dispatched();
    }

    /**
     * Output dormant tasks
     */
    protected function dormant()
    {
        $this->log('------------------------------------------------------------');
        $this->log(' DORMANT ' . CURRENT_TIME);
        $this->log('------------------------------------------------------------');

        foreach ($this->dormantTasks() as $task) {
            $this->log(' > ' . $task);
        }

        $this->log('');
    }

    /**
     * Output dispatched tasks
     */
    protected function dispatched()
    {
        $this->log('------------------------------------------------------------');
        $this->log(' DISPATCHED ' . CURRENT_TIME);
        $this->log('------------------------------------------------------------');

        foreach ($this->dispatchedTasks() as $task) {
            $this->log(' > ' . $task);
        }

        $this->log('');
    }
}