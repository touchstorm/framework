<?php

namespace Chronos\Dispatchers;

use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Chronos\Queues\Queue;
use Closure;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Batches
 * @package Chronos\Dispatchers
 */
class Batches extends Dispatcher
{
    /**
     * @var int $batchSize
     * - Default defined as 0 which disables
     * the batching.
     */
    protected $batchSize = 0;

    /**
     * Threads constructor.
     * @param QueueRepositoryContract $repository
     */
    public function __construct(QueueRepositoryContract $repository)
    {
        parent::__construct($repository);

        // Set the batch size configuration
        $this->batchSize = $this->repository->getBatchSize();

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
             * @var Queue $queue
             */
            while ($batch = $this->repository->next()) {

                // Govern the amount of threads that
                // are running based on maxThreads
                $this->governor();

                // Execute and track thread
                $this->executeThreadBatch($batch, $options);

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
     * @param Collection $batch
     * @param array $options
     */
    protected function executeThreadBatch(Collection $batch, Array $options = [])
    {
        // Create the argument vectors
        $vectors = $this->createArgumentVectors($batch, $options);

        // Create the command string
        $batchCommand = $this->createBatchCommand($vectors);

        // Open the process
        $process = proc_open($batchCommand, $this->descriptors, $pipes, null, null);

        // Process failed to open
        if (!$process) {
            $this->log('Process failed to open');
            return;
        }

        // Get the status
        $status = proc_get_status($process);

        // Add the pid to the process id container
        // which is keyed to the Queue as value
        $this->processIds[$status['pid']] = $batch;

        // Add the process resource to the container
        $this->processes[] = $process;
    }

    /**
     * Create the final batch command to dispatch to the console
     * @param array $vectors
     * @return string $command
     */
    protected function createBatchCommand(array $vectors)
    {
        $base_dir = getenv('APP_BASE');
        $log_dir = getenv('APP_LOGS');

        // Define the executable command
        $path = "php " . $base_dir . "/dispatch/batch.php";
        $argv = implode(' ', $vectors);
        $output = $log_dir . '/' . $this->repository->getLogFile();

        // Create full thread command
        $command = $path . ' ' . $argv;

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

        $this->log($command);

        $this->logDispatchedCommands($command);

        return $command;
    }

    /**
     * Create the argument vectors for the command
     * @param Collection $batch
     * @param array $options
     * @return array
     */
    protected function createArgumentVectors(Collection $batch, array $options = [])
    {
        $vectors = $ids = [];

        // Array map through the collection and
        // update the ids array
        $batch->map(function ($queue) use (&$ids) {
            $ids[] = $queue->getThreadArgument('id');
        });

        // string ids together to form an id vector for the command
        $vectors[] = implode('~', $ids);

        // Add any additional argument vectors
        if (isset($options['vectors'])) {
            $vectors = array_merge($vectors, $options['vectors']);
        }

        // Return if we're doing a dry run
        if ($this->dryRun) {
            // reduce the batch.
            $this->repository->reduce($ids);
        }

        return $vectors;
    }
}