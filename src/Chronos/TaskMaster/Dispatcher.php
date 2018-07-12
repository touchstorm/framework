<?php

namespace Chronos\TaskMaster;

use Chronos\TaskMaster\Contracts\TaskMasterContract;
use Chronos\Tasks\Task;

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
            if (!$task->isTask('scheduled')) {
                continue;
            }

            // If it is currently running or not available, skip
            if ($this->isRunning($task) || !$task->isAvailable()) {
                $this->collectDormantTask($task);
                continue;
            }

            // Pre dispatch commands
            if ($task->hasBeforeCommands()) {
                $this->execute($task->getBeforeCommands(), $task, 'before');
            }

            // Execute the task's main command
            $this->execute($task->getCommand(), $task);

            // Post dispatch commands
            if ($task->hasAfterCommands()) {
                $this->execute($task->getAfterCommands(), $task, 'after');
            }

            // trigger taskStartEvent()
        }

        // Output reports
        $this->dormant();
        $this->dispatched();
    }

    /**
     * Detect which type of command
     * - bash/command line
     * - controller/method
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

        foreach ($commands as $command) {

            $this->collectDispatchedTask($task, $type, $command);

            $output = [];

            exec($command, $output);

            $output = !empty($output) ? $output : ['asynchronous'];

            $this->collectOutputs($task, $type, $output);
        }

        return;
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

        // Display before
        foreach ($this->dispatchedTasks() as $name => $dispatches) {

            $this->log(' > ' . $name);

            foreach ($dispatches as $type => $dispatched) {

                foreach ($dispatched as $index => $dispatch) {

                    if ($type == 'command') {
                        $this->log("    > " . $type . ' ' . $dispatch['task']);
                        continue;
                    }

                    $this->log("    > " . $type . ' dispatch command: ' . $dispatch['command']);
                }
            }

            $this->log('------------------------------------------------------------');
        }

        $this->log('');
    }
}