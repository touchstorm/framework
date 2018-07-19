<?php

namespace Chronos\Queues\Contracts;

interface QueueContract
{
    function threadArguments();

    function reschedule($date);

    function remove();
}