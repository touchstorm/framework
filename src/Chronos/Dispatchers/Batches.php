<?php

namespace Chronos\Dispatchers;

use Chronos\Repositories\BatchQueueRepository;
use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Chronos\Queues\Queue;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class Batches
 * @package Chronos\Dispatchers
 */
class Batches
{
    /**
     * @var int $maxThreads
     * - Default value of threads for the system
     */
    protected $maxThreads = 1;

    /**
     * @var int $batchSize
     * - Default defined as 0 which disables
     * the batching.
     */
    protected $batchSize = 0;

    /**
     * @var array $processes
     * - An array of active processes
     */
    protected $processes = [];

    /**
     * @var array $processIds
     * - An array of Queue Items keyed
     * by the process ids running on the system.
     * [[## => QueueItem],...]
     */
    protected $processIds = [];

    /**
     * @var array $descriptors
     * - Output descriptors
     */
    protected $descriptors = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "r"]
    ];

    /**
     * @var string $logFile
     * - Default log file
     */
    protected $logFile = 'default.log';

    /**
     * @var $threadFile
     */
    protected $threadFile;

    /**
     * @var bool $verbose
     * - Outputs processing to logs
     */
    protected $verbose = true;

    /**
     * @var bool $disabled
     * - Allows for the file to be rendered
     * disabled but still in process.
     */
    protected $disabled = false;

    /**
     * @var BatchQueueRepository $repository
     * - The queue repository feeds the thread dispatcher
     * with the needed queue items.
     */
    protected $repository;

    /**
     * @var bool $emptied
     * - Default (false) Continuously run threads as they become
     * available in the batch container. (Default)
     *
     * - (true) Waits until all processes have completed
     * and the batch is empty before refilling
     * the batch container.
     */
    protected $emptied = false;

    /**
     * @var bool $dryRun
     * - IF true then the dispatcher will go through
     * all the motions but will not execute the command.
     */
    protected $dryRun = false;

    /**
     * Threads constructor.
     * @param QueueRepositoryContract $repository
     */
    public function __construct(QueueRepositoryContract $repository)
    {
        // Set the injected repository
        $this->repository = $repository;

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
    public function handle($options = [])
    {
        $this->log("Handling...");

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
        // Create the command string
        $threadCommand = $this->createBatchCommand($batch, $options);

        // Output
        $this->log($threadCommand);

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
        $this->processIds[$status['pid']] = $batch;

        // Add the process resource to the container
        $this->processes[] = $process;
    }

    /**
     * @param Collection $batch
     * @param array $options
     * - Create the string command from
     * batch and option vectors.
     * @return string $command
     */
    protected function createBatchCommand(Collection $batch, Array $options)
    {
        // Set directory variables
        $base_dir = getenv('APP_BASE');
        $log_dir = getenv('APP_LOGS');

        $vectors = $ids = [];

        /** @var Queue $queue */
        foreach ($batch as $queue) {

            // Default argument vectors
            $ids[] = $queue->getThreadArgument('id');
        }

        $vectors[] = implode('~', $ids);

        // Add any additional argument vectors
        if (isset($options['vectors'])) {
            $vectors = array_merge($vectors, $options['vectors']);
        }

        // Define the executable command
        $path = "php " . $base_dir . "/dispatch/thread.php";
        $argv = implode(' ', $vectors);
        $output = $log_dir . '/' . $this->repository->getLogFile();

        // Create full thread command
        $command = $path . ' ' . $argv;

        // Return if we're doing a dry run
        if ($this->dryRun) {

            // Hijack the command
            $command = 'echo "testBatch"' . ' ' . $argv;

            // Null out the log file
            $this->logFile = null;

            // reduce the batch.
            $this->repository->reduce($ids);
        }

        if ($this->logFile) {
            $command . ' >> ' . $output;
        }

        return $command;
    }


    /**
     * Pauses dispatcher until all processes
     * have finished running.
     * @return void
     */
    protected function untilEmpty()
    {
        // If continuous return and keep going.
        if (!$this->emptied) {
            return;
        }

        // Pause for the processes to finish, then continue
        while ($this->processing()) {
            $this->log('Processing...', false);
            $this->processReduce();
        }
    }

    /**
     * Governor that limits the amount
     * of running threads to the maxThreads
     * @return void
     */
    protected function governor()
    {
        while (($processing = count($this->processes)) >= $this->maxThreads) {
            $this->log('Governing threads (' . $processing . ') processes', false);
            $this->processReduce();
        }
    }

    /**
     * Pause between thread executions
     */
    protected function pause()
    {
        $pause = $this->repository->getPause();

        if ($pause == 0) {
            return;
        }

        $this->log('Pausing for ' . $pause . ' microseconds...');

        usleep($pause);
    }

    /**
     * Detect process which have finished
     * Reduce the processes containers
     */
    protected function processReduce()
    {
        // Get the current processes
        $resources = array_keys($this->processes);

        // Loop through and get status'
        // reduce where process has finished
        for ($i = 0; $i < count($this->processes); $i++) {

            // Get the status on the current process from resources
            $status = proc_get_status($this->processes[$resources[$i]]);

            // If running continue
            if ($status['running']) {
                continue;
            }

            // Close the process out
            proc_close($this->processes[$resources[$i]]);

            // Reduce the containers
            unset($this->processes[$resources[$i]]);
            unset($this->processIds[$status['pid']]);
        }
    }

    ////////////////////////////////////
    // Status methods
    ////////////////////////////////////

    /**
     * @return int
     */
    protected function processing()
    {
        return (int)count($this->processes);
    }

    /**
     * Is it disabled
     * @return void
     */
    protected function disabled()
    {
        // While disabled always loop
        while ($this->disabled) {
            $this->log($this->name() . ' is disabled...');
            sleep(10);
            continue;
        }
    }

    /**
     * Is system sleeping
     * @return void
     * @throws \Chronos\Queues\QueueException
     */
    public function sleeping()
    {
        if ($this->dryRun) {
            return;
        }

        // If done stop crawling
        while ($this->repository->isSleeping()) {

            // Tidy up the remaining threads
            $this->processReduce();

            // Since we're sleeping lets try
            // and fill the batch and wake up
            if ($this->repository->fill()) {
                $this->log($this->name() . " batch filled!");
                return;
            }

            $this->log($this->name() . " cron is sleeping...");
            sleep(5);
            continue;
        }
    }

    ////////////////////////////////////
    // Configuration Setter methods
    ////////////////////////////////////

    /**
     * Set thread path
     * @param $thread
     */
    protected function _thread($thread)
    {
        $dir = __DIR__ . '/../Threads/';
        $this->threadFile = $dir . $thread;
    }

    /**
     * Set log path
     * @param $log
     */
    protected function _log($log)
    {
        $dir = __DIR__ . '/../Logs/';
        $this->logFile = $dir . $log;
    }

    /**
     * Load the queue
     * @param QueueRepositoryContract $repository
     */
    protected function _repository(QueueRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Override the default to dry run
     * the commands.
     * @param bool $value
     */
    public function setDryRun($value = false)
    {
        $this->dryRun = $value;
    }

    /**
     * Return the dry run value
     * @return bool
     */
    public function getDryRun()
    {
        return $this->dryRun;
    }

    /**
     * Run each loop until batch is empty
     * before refilling the batch from the repository
     * the commands.
     */
    public function runUntilEmpty()
    {
        $this->setEmptied(true);
    }

    /**
     * Set the empty value
     * @param bool $value
     */
    public function setEmptied($value = false)
    {
        $this->emptied = $value;
    }

    /**
     * Get empty value
     * @return bool
     */
    public function getEmptied()
    {
        return $this->emptied;
    }

    /**
     * Make the application talk
     */
    public function squawk()
    {
        $this->setVerbose(true);
    }

    /**
     * Set the verbose value
     * - default (false)
     * @param bool $value
     */
    public function setVerbose($value = false)
    {
        $this->verbose = $value;
    }

    /**
     * Get the verbose settings
     * @return bool
     */
    public function getVerbose()
    {
        return $this->verbose;
    }

    /**
     * Set cron name
     * @return string
     */
    protected function name()
    {
        return __CLASS__;
    }

    ////////////////////////////////////
    // Public getters
    ////////////////////////////////////

    public function getProcesses()
    {
        return $this->processes;
    }

    public function getProcessIds()
    {
        return $this->processIds;
    }

    ////////////////////////////////////
    // Reporting methods
    ////////////////////////////////////

    /**
     * Log output
     * @param $msg
     * @param bool $return
     */
    protected function log($msg, $return = true)
    {
        if (!$this->verbose) {
            return;
        }

        echo $msg . (($return) ? "\n" : "\r");
    }

}