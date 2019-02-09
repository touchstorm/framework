<?php

namespace Chronos\Dispatchers;

use Chronos\Dispatchers\Contracts\DispatcherContract;
use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\Queue;

/**
 * Class Threads
 * @package Chronos\Dispatchers
 */
class Threads extends Dispatcher implements DispatcherContract
{
    /**
     * Threads constructor.
     * @param QueueRepositoryContract $repository
     */
    public function __construct(QueueRepositoryContract $repository)
    {
        // Set the injected repository
        parent::__construct($repository);

        // Set max thread configuration
        $this->maxThreads = $this->repository->getMaxThreads();
    }

    /**
     * Execute the thread handler
     *
     * @param array $options
     * @throws \Chronos\Queues\QueueException
     */
    public function handle(array $options = [])
    {

        $this->configure($options);

        // Always Running
        while (true) {

            // Is the system disabled
            $this->disabled();

            // When there is nothing in queue to run
            $this->sleeping();

            /**
             * Pop next Queue item out of
             * the repository's batch and execute
             * @var QueueContract $queue
             */
            while ($queue = $this->repository->next()) {

                // Govern the amount of threads that
                // are running based on maxThreads
                $this->governor();

                // Execute and track thread
                $this->executeThread($queue, $options);

                // Pause between threads
                $this->pause();
            }

            // Wait until batch is empty
            // before retrieving the next batch
            $this->untilEmpty();

            // Break on a dry run
            if ($this->dryRun) {
                break;
            }
        }
    }

    /**
     * Execute the threading process
     * - Send thread off as its own process with arg vectors
     * - Get status and
     * @param QueueContract $queue
     * @param array $options
     */
    protected function executeThread(QueueContract $queue, Array $options = [])
    {
        // Create the argument vectors for the command
        $vectors = $this->createArgumentVectors($queue, $options);

        $threadCommand = $this->createThreadCommand($vectors);

        // Open the process
        $process = proc_open($threadCommand, $this->descriptors, $pipes, null, null);


        // Process failed to open
        if (!$process) {
            $this->log('Process failed to open');
            return;
        }

        // Get the status
        $status = proc_get_status($process);

        // Add the pid to the process id container
        // which is keyed to the Queue as value
        $this->processIds[$status['pid']] = $queue;

        // Add the process resource to the container
        $this->processes[] = $process;
    }

    /**
     * Create argument vectors for the thread/batch
     * @param QueueContract $queue
     * @param array $options
     * @return array $vectors
     */
    protected function createArgumentVectors(QueueContract $queue, array $options)
    {
        // Default argument vectors
        $vectors = $queue->threadArguments();

        // Add any additional argument vectors
        if (isset($options['vectors'])) {
            $vectors = array_merge($vectors, $options['vectors']);
        }

        return $vectors;
    }

    /**
     * Create the thread command to be dispatched to the console
     * @param array $vectors
     * @return string
     */
    protected function createThreadCommand(array $vectors = [])
    {
        // Set directory variables
        $base_dir = getenv('APP_BASE');
        $log_dir = getenv('APP_LOGS');

        // Define the executable command
        $path = "php " . $base_dir . "/dispatch/thread.php";
        $threadVectors = implode(' ', $vectors);
        $output = $log_dir . '/' . $this->repository->getLogFile();

        // Final command
        $command = $path . ' ' . $threadVectors;

        // Return if we're doing a dry run
        if ($this->dryRun) {

            // Hijack the command
            $command = 'echo "' . $command . '"';

            // Null out the log file
            $this->logFile = null;
        }

        if ($this->logFile) {
            $command . ' >> ' . $output;
        }

        // Output
        $this->log($command);

        // Collect command
        $this->logDispatchedCommands($command);

        // Create full thread command
        return $command;
    }


}