<?php

namespace Chronos\Tasks;

/**
 * Class Task
 * @package Chronos\Application\Tasks
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
    protected $allowed = ['name', 'service', 'type', 'command'];

    /**
     * @var array $aliases
     * - Alias from the task definition.
     * - Set in ~/Task.php
     */
    protected $aliases = ['uses' => 'service'];

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
        return $this->service;
    }

    /**
     * @return mixed
     * - Get the specified command to run
     */
    public function getCommand()
    {
        return $this->command;
    }
}