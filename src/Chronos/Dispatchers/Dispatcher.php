<?php

namespace Chronos\Dispatchers;

use Chronos\Repositories\Contracts\QueueRepositoryContract;
use Closure;

abstract class Dispatcher
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
     * @var array $dispatchedCommand
     * - An array of the dispatched commands
     * Note: this is for testing purposes.
     * Collecting commands on a live dispatcher will
     * cause the class to grow until you run out of memory.
     */
    protected $dispatchedCommands = [];

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
     * @var bool
     * - If true the dispatcher will collect the dispatched
     * command and store them in the dispatchedCommands class
     * variable. This should only be true for unit testing.
     */
    protected $collectCommands = false;

    /**
     * Threads constructor.
     * @param QueueRepositoryContract $repository
     */
    public function __construct(QueueRepositoryContract $repository)
    {
        // Set the injected repository
        $this->repository = $repository;
    }

    /**
     * Threaded dispatchers all need
     * defined handler methods.
     * @param array $options
     * @return mixed
     */
    abstract public function handle(array $options = []);

    /**
     * Configure the class
     * @param array $options
     */
    protected function configure(array $options)
    {
        if (!isset($options['settings'])) {
            return;
        }

        foreach ($options['settings'] as $setter => $value) {

            // Parse closure settings and continue
            if ($value instanceof Closure) {
                $value($this);
                continue;
            }

            // if it is a setter getMethod set and continue
            if (method_exists($this, $setter)) {
                call_user_func([$this, $setter], $value);
                continue;
            }
        }

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

    /**
     * Log a dispatched command
     * @param string $command
     */
    protected function logDispatchedCommands(string $command = '')
    {
        if (!$this->collectCommands) {
            return;
        }

        $this->dispatchedCommands[] = $command;
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
    // Getter methods
    ////////////////////////////////////

    /**
     * Return the dry run value
     * @return bool
     */
    public function getDryRun()
    {
        return $this->dryRun;
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
     * Get the verbose settings
     * @return bool
     */
    public function getVerbose()
    {
        return $this->verbose;
    }

    /**
     * Get all dispatched commands
     * @return array
     */
    public function getDispatchedCommands()
    {
        return $this->dispatchedCommands;
    }

    /**
     * Get dispatched command
     * @param int $index
     * @return mixed
     */
    public function getDispatchedCommand($index = 0)
    {
        return $this->dispatchedCommands[$index];
    }

    /**
     * Get the collectCommands value
     * @return bool
     */
    public function getCollectCommands()
    {
        return $this->collectCommands;
    }

    /**
     * Get the repository
     * @return QueueRepositoryContract|QueueRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Get the current dispatched processes
     * @return array
     */
    public function getProcesses()
    {
        return $this->processes;
    }

    /**
     * Get the process ids
     * @return array
     */
    public function getProcessIds()
    {
        return $this->processIds;
    }

    ////////////////////////////////////
    // Setter methods
    ////////////////////////////////////

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
     * Set the collectCommands value
     * NOTE: Default is false and live applications
     * should always be on default. If true the dispatchedCommands
     * array will fill and cause PHP to run out of run time memory.
     * This is a setter solely for testing dispatch outputs to make
     * sure they are assertable.
     * @param bool $value
     */
    public function setCollectCommands($value = false)
    {
        $this->collectCommands = $value;
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