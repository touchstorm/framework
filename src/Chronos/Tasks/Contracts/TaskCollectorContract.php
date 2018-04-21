<?php

namespace Chronos\Tasks\Contracts;

interface TaskCollectorContract
{
    function running($name, $options);

    function scheduled($name, $options);

    function addRoute($name, TaskContract $route);
}