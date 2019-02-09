<?php

namespace Chronos\TaskMaster\Contracts;

interface TaskMasterContract
{
    public function dispatch(array $options);
}