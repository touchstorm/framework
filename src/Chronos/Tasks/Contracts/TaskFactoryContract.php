<?php

namespace Chronos\Tasks\Contracts;

interface TaskFactoryContract
{
    function running($name, $parameters);

    function scheduled($name, $parameters);
}