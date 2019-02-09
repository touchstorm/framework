<?php

namespace Chronos\Controllers\Contracts;

interface ThreadContract
{
    /**
     * All Thread and Batch threads require a handle method for dispatching
     * @return void
     */
    function handle();
}