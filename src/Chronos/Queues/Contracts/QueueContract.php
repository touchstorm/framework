<?php

namespace Chronos\Queues\Contracts;

interface QueueContract
{
    function threadArguments();

    function reschedule($date);

    function completed($reschedule, $date);

    function remove();
}