<?php

namespace Chronos\TaskMaster;

use Chronos\TaskMaster\Contracts\TaskMasterContract;
use Chronos\Tasks\Task;

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
        $this->log('////////////////////////////////////////////////////////////');
        $this->log(' Watcher ' . CURRENT_TIME);
        $this->log('////////////////////////////////////////////////////////////');

        /**
         * @var string $name
         * @var \Chronos\Tasks\Running $task
         */
        foreach ($this->taskCollector->getTasks() as $name => $task) {

            // Skip all but running tasks, skip
            if (!$task->isTask('running')) {
                continue;
            }

            // If task is already operating on the system, skip
            if ($this->isRunning($task)) {
                $this->collectRunningTask($task);
                continue;
            }

            // Execute the task's main command
            $this->execute($task->getCommand(), $task);
        }

        $this->running();
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
    protected function running()
    {
        $this->log('------------------------------------------------------------');
        $this->log(' RUNNING ' . CURRENT_TIME);
        $this->log('------------------------------------------------------------');

        foreach ($this->runningTasks() as $task) {
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