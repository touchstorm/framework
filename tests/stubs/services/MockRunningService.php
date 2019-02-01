<?php

use Chronos\Services\ThreadedService;

class RunningServiceUnitTest extends ThreadedService
{
    protected $repository = MockRunningRepository::class;

    public function running()
    {

    }

    public function thread()
    {

    }
}