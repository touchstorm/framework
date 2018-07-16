<?php

namespace Chronos\Tasks;

/**
 * Class Task
 * @package Chronos\Tasks
 */
class Task
{
    /**
     * @var string $name
     * - Simple task name
     */
    protected $name;

    /**
     * @var string $service
     * - The name of the service that
     * will bind the dependencies for this task
     */
    protected $service;

    /**
     * @var array $allowed
     * - Allowed parameters for the task
     */
    protected $allowed = ['name', 'service', 'type', 'command', 'controlCommand', 'asynchronous'];

    /**
     * @var array $aliases
     * - Alias from the task definition.
     * - Set in ~/Task.php
     */
    protected $aliases = ['uses' => 'service', 'async' => 'asynchronous'];

    /**
     * @var string $type
     * - The type of task this is, running/scheduled
     * - Set in ~/Task.php
     */
    protected $type;

    /**
     * Command to run if set
     * @var mixed $command
     */
    protected $command = null;

    /**
     * Command to run via controller
     * @var mixed $command
     */
    protected $controlCommand = null;

    /**
     * One off command to be executed before
     * the scheduled task is run
     * @var $before null
     */
    protected $before = null;

    /**
     * One off command to be executed after
     * the scheduled task is run
     * @var $after null
     */
    protected $after = null;

    /**
     * Whether or not the task is asynchronous
     * @var bool
     */
    protected $asynchronous = true;

    /**
     * Task constructor.
     * @param $name
     * @param array $arguments
     */
    public function __construct($name, Array $arguments = [])
    {
        // Set the name
        $this->name = $name;

        // Resolve the configuration
        $this->configure($arguments);
    }

    /**
     * Configure
     * - Configure this task based on the
     * arguments set up on ~/Task.php
     * @param array $arguments
     */
    private function configure(Array $arguments = [])
    {
        /**
         * @var string $alias
         * @var string $argument
         */
        foreach ($arguments as $alias => $argument) {

            // Check if if aliased
            $alias = (isset($this->aliases[$alias])) ? $this->aliases[$alias] : $alias;

            // If not allowed skip
            if (isset($this->allowed[$alias])) {
                continue;
            }

            // If the property exits on the class
            // allow it to be set
            if (property_exists($this, $alias)) {
                $this->$alias = $argument;
            }
        }
    }

    /**
     * Is this a scheduled task
     * @param string $type
     * @return bool
     */
    public function isTask($type)
    {
        if (empty($type)) {
            return false;
        }

        return ($this->type == $type) ? true : false;
    }

    /**
     * Does this task have before commands?
     * @return bool
     */
    public function hasBeforeCommands()
    {
        return !!$this->before;
    }

    /**
     * Does this task have after commands?
     * @return bool
     */
    public function hasAfterCommands()
    {
        return !!$this->after;
    }

    /**
     * Miscellaneous commands to run as one-off commands after
     * the scheduled task is complete.
     * @param $commands
     * @return $this
     */
    public function after($commands)
    {
        $this->after = $commands;

        return $this;
    }

    /**
     * Miscellaneous tasks to run as one-off tasks after
     * the scheduled task is complete.
     * @param $commands
     * @return $this
     */
    public function before($commands)
    {
        $this->before = $commands;

        return $this;
    }

    /////////////////////////////////////////////
    /// Getters
    /////////////////////////////////////////////

    /**
     * @return string running|scheduled
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     * - The name of this Task
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     * - The name of the service used
     * to bind dependencies into the IoC container
     */
    public function getService()
    {
        return ($this->service) ?: null;
    }

    /**
     * One off tasks to run before
     * scheduled task execution
     * @return string|array|null
     */
    public function getBeforeCommands()
    {
        if (!$this->before) {
            return $this->before;
        }

        if (is_string($this->before)) {
            return [$this->makeCommand($this->before, false)];
        }

        $commands = [];

        foreach ($this->before as $command) {
            $commands[] = $this->makeCommand($command, false);
        }

        return $commands;
    }

    /**
     * One off tasks to run after
     * scheduled task execution
     * @return string|array|null
     */
    public function getAfterCommands()
    {
        if (!$this->after) {
            return $this->after;
        }

        if (is_string($this->after)) {
            return [$this->makeCommand($this->after, false)];
        }

        $commands = [];

        foreach ($this->after as $command) {
            $commands[] = $this->makeCommand($command, false);
        }

        return $commands;
    }

    /**
     * @return mixed
     * - Get the specified command to run
     */
    public function getCommand()
    {
        return [$this->makeCommand(($this->command) ?: $this->controlCommand)];
    }

    /**
     * Create the command to be executed
     * @param $command
     * @param bool $async
     * @return string
     */
    protected function makeCommand($command, $async = true)
    {
        return ($this->asynchronous && $async) ? 'nohup ' . $command . ' > /dev/null 2>&1 &' : $command;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName() . "\t| Scheduled: " . $this->runs . "\t| Command: " . $this->getCommand()[0];
    }
}