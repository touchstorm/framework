<?php

namespace Chronos\TaskMaster;

use Chronos\TaskMaster\Contracts\TaskMasterContract;
use Chronos\Tasks\Task;
use LucidFrame\Console\ConsoleTable;

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
     * Output dormant tasks
     */
    protected function running()
    {
        $this->log('RUNNING ');

        $table = new ConsoleTable();
        $table->addHeader('Tasks')->addHeader('type')->addHeader('Schedule')->addHeader('Command');

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

        $this->log('');
    }

    /**
     * Output dispatched tasks
     */
    protected function dispatched()
    {
        $this->log('DISPATCHED Tasks');

        $table = new ConsoleTable();
        $table->addHeader('Tasks')->addHeader('type')->addHeader('Schedule')->addHeader('Command');

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

        $this->log('');
    }

}