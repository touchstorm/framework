<?php

namespace Chronos\Application\Tasks\Contracts;

interface TaskFactoryContract
{
    function running($name, $parameters);

    function scheduled($name, $parameters);
}