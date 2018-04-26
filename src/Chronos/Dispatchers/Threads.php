<?php

namespace Chronos\Dispatchers;

use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Chronos\Queues\Contracts\QueueContract;
use Chronos\Queues\Queue;

/**
 * Class Threads
 * @package Chronos\Dispatchers
 */
class Threads
{
    /**
     * @var int $maxThreads
     * - Default value of threads for the system
     */
    protected $maxThreads = 1;

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
     * @var QueueRepositoryContract $repository
     * - The queue repository feeds the thread dispatcher
     * with the needed queue items.
     */
    protected $repository;

    /**
     * @var bool $emptied
     * - Continuously run threads as they become
     * available in the batch container. (Default)
     *
     * - Or wait until all processes have completed
     * and the batch is empty before refilling
     * the batch container.
     */
    protected $emptied = false;

    /**
     * Threads constructor.
     * @param QueueRepositoryContract $repository
     */
    public function __construct(QueueRepositoryContract $repository)
    {
        // Output
        $this->log('Initializing...');

        // Set the injected repository
        $this->repository = $repository;

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

        // Temp reset
        $this->log("Reset to tomorrow");
        $this->repository->reset('*', [
            'available_at' => (new \DateTime('yesterday'))
        ]);

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
            while ($queue = $this->repository->next()) {

                // Govern the amount of threads that
                // are running based on maxThreads
                $this->governor();

                // Execute and track thread
                $this->executeThread($queue, $options);
            }

            // Wait until batch is empty
            // before retrieving the next batch
            $this->untilEmpty();
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
        // Set directory variables
        $base_dir = getenv('APP_BASE');
        $log_dir = getenv('APP_LOGS');

        // Default argument vectors
        $vectors = $queue->threadArguments();

        // Add any additional argument vectors
        if (isset($options['vectors'])) {
            $vectors = array_merge($vectors, $options['vectors']);
        }

        // Define the executable command
        $threadPath = "php " . $base_dir . "/dispatch/thread.php";
        $threadVectors = implode(' ', $vectors);
        $threadOutput = $log_dir . '/' . $this->repository->getLogFile();

        // Create full thread command
        $threadCommand = $threadPath . ' ' . $threadVectors . ' >> ' . $threadOutput;

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
        $this->processIds[$status['pid']] = $queue;

        // Add the process resource to the container
        $this->processes[] = $process;
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

            // Get the Queue item out of the processes container
            $queue = $this->processIds[$status['pid']];

            // Reschedule the queue item
            $this->repository->reschedule($queue);

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
     * Set cron name
     * @return string
     */
    protected function name()
    {
        return __CLASS__;
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