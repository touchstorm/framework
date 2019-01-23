<?php

namespace Chronos\Tasks;

use Chronos\Tasks\Exceptions\TaskCollectionException;

class Batch extends Task
{
    /**
     * Running constructor.
     * @param $name
     * @param $arguments
     * @throws TaskCollectionException
     */
    public function __construct($name, $arguments = null)
    {
        if (is_null($arguments)) {
            throw new TaskCollectionException('Task cannot be initiated. Missing arguments' . 422);
        }

        // If arguments come over as an array
        if (is_array($arguments)) {
            parent::__construct($name, $arguments);
            return;
        }

        // Default assumes a string
        parent::__construct($name, ['uses' => $arguments]);
    }
}