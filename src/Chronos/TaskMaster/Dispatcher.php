<?php

namespace Chronos\TaskMaster;

use Chronos\TaskMaster\Contracts\TaskMasterContract;
use Chronos\Tasks\Task;
use LucidFrame\Console\ConsoleTable;

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
        $this->log(' Scheduled Tasks ' . CURRENT_TIME);
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
     * Output dormant tasks
     */
    protected function dormant()
    {
        $this->log('DORMANT Tasks');

        $table = new ConsoleTable();
        $table->addHeader('Tasks')->addHeader('type')->addHeader('Schedule')->addHeader('Command');

        foreach ($this->dormantTasks() as $task) {
            $arr = $task->toArray();

            $table = $table->addRow()
                ->addColumn($arr['name'])
                ->addColumn($arr['type'])
                ->addColumn($arr['schedule'])
                ->addColumn($arr['command'][0]);

        }

        if ($this->verbose) {

            if (empty($this->dormantTasks())) {
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