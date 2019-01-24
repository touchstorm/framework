<?php

namespace Chronos\Kernel;

class Chronos extends Kernel
{

    // TODO
    // Get task collector from IoC
    // parseInput tasks from directory
    // dispatch running asynchronous
    // dispatch scheduled i/o blocking and asynchronous
    public function toDo()
    {
        /*
 * -------------------------------------------------
 * Create Task Collector
 * -------------------------------------------------
 * - Collect all user defined tasks for dispatch
 */
        $task = $app->make(\Chronos\Tasks\TaskCollector::class);

        /*
         * -------------------------------------------------
         * Load the task routing definitions & share
         * -------------------------------------------------
         */

        $taskDirector = __DIR__ . '/tasks/';
        $taskFiles = array_diff(scandir($taskDirector), ['..', '.']);

        foreach ($taskFiles as $file) {

            if (substr_compare($file, 'Tasks.php', -strlen('Tasks.php')) === 0) {
                require_once($taskDirector . DIRECTORY_SEPARATOR . $file);
                $app->share($task);
            }
        }

// TODO
// Implement Amphp for asynchronous event dispatching

        /*
         * -------------------------------------------------
         * Task Watcher (Tasks)
         * -------------------------------------------------
         * - Checks all running dispatch.
         * - Relaunches any running dispatch that may have stopped
         */
        $app->execute([\App\Dispatchers\Running::class, 'dispatch']);

        /*
         * -------------------------------------------------
         * Task Scheduled (Tasks)
         * -------------------------------------------------
         * - Checks server datetime and dispatch available crons
         */
        $app->execute([\App\Dispatchers\Scheduled::class, 'dispatch']);
    }

}