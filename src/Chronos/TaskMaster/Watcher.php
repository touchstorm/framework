<?php

namespace Chronos\TaskMaster;

use Chronos\TaskMaster\Contracts\TaskMasterContract;
use Chronos\Tasks\Task;
use LucidFrame\Console\ConsoleTable;

/**
 * Class Watcher
 * The Watcher's job is to make sure all Running and Batch Dispatchers are up.
 * If it finds a dispatcher that has gone down, because of a kill command or a server reboot.
 * The watcher will dispatch it to run on the server again.
 *
 * These dispatchers are listeners that will look at the queues and dispatch threads.
 * If they go down, Threads aren't dispatched.
 *
 * @package Chronos\TaskMaster
 */
class Watcher extends BaseTaskMaster implements TaskMasterContract
{
    /**
     * Dispatch task to be processed
     * @param array $options
     */
    public function dispatch(array $options = [])
    {
        // Pass through optional inputs for configuration
        $this->configure($options);

        // Echo out info to the console
        $this->preamble();

        // Dispatch running tasks
        $this->dispatchRunningTasks();

        // Dispatch batch running tasks
        $this->dispatchBatchRunningTasks();

        // Outputs to console
        $this->running();
        $this->log('');
        $this->batch();
        $this->log('');
        $this->dispatched();
    }

    /**
     * Dispatch all running tasks
     */
    protected function dispatchRunningTasks()
    {
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
    }

    /**
     * Dispatch all batch running tasks
     */
    protected function dispatchBatchRunningTasks()
    {
        /**
         * @var string $name
         * @var \Chronos\Tasks\Batch $task
         */
        foreach ($this->taskCollector->getTasks() as $name => $task) {

            // Skip all but running tasks, skip
            if (!$task->isTask('batch')) {
                continue;
            }

            // If task is already operating on the system, skip
            if ($this->isRunning($task)) {
                $this->collectBatchTask($task);
                continue;
            }

            // Execute the task's main command
            $this->execute($task->getCommand(), $task);
        }
    }

    /**
     * Watcher information for the console
     */
    protected function preamble()
    {
        $this->log('+--------------------------------------------------+');
        $this->log('| Chronos / Watcher /' . CURRENT_TIME) . ' | ';
        $this->log('+--------------------------------------------------+');
        $this->log('| The Watcher ensures your Running Tasks stay alive.');
        $this->log('| If the task fails, is killed, the server reboots,');
        $this->log('| the Watcher will dispatch the running task');
        $this->log('| automatically on it\'s next cycle.');
        $this->log('+--------------------------------------------------+');
        $this->log('');
    }

    /**
     * Output all running tasks
     */
    protected function running()
    {

        $table = new ConsoleTable();
        $table->addHeader('Running Tasks (running)')->addHeader('type')->addHeader('Schedule')->addHeader('Command');

        foreach ($this->runningTasks() as $task) {
            $arr = $task->toArray();

            $table = $table->addRow()
                ->addColumn($arr['name'])
                ->addColumn($arr['type'])
                ->addColumn($arr['schedule'])
                ->addColumn($arr['command'][0]);
        }

        if ($this->verbose) {

            if (empty($this->runningTasks())) {
                $table = $table->addRow(['None', '-', '-', '-']);
            }

            $table->display();
        }

    }

    /**
     * Output batch tasks
     */
    protected function batch()
    {
        $table = new ConsoleTable();
        $table->addHeader('Batched Tasks (running)')->addHeader('type')->addHeader('Schedule')->addHeader('Command');

        foreach ($this->batchTasks() as $task) {
            $arr = $task->toArray();

            $table = $table->addRow()
                ->addColumn($arr['name'])
                ->addColumn($arr['type'])
                ->addColumn($arr['schedule'])
                ->addColumn($arr['command'][0]);
        }

        if ($this->verbose) {

            if (empty($this->batchTasks())) {
                $table = $table->addRow(['None', '-', '-', '-']);
            }

            $table->display();
        }

    }

    /**
     * Output dispatched tasks
     */
    protected function dispatched()
    {
        $table = new ConsoleTable();
        $table->addHeader('Dispatched Tasks')->addHeader('type')->addHeader('Schedule')->addHeader('Command');

        // Display before
        foreach ($this->dispatchedTasks() as $name => $dispatches) {

            foreach ($dispatches as $type => $dispatched) {

                foreach ($dispatched as $index => $dispatch) {

                    $arr = $dispatch['task']->toArray();

                    $table = $table->addRow()
                        ->addColumn($arr['name'])
                        ->addColumn($type)
                        ->addColumn($arr['schedule'])
                        ->addColumn($dispatch['command']);

                }
            }

            $table = $table->addBorderLine();
        }

        if ($this->verbose && !empty($this->dispatchedTasks())) {
            $table->display();
        }

    }

}