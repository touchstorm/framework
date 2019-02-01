<?php

use Chronos\Services\ThreadedService;

class MockRunningService extends ThreadedService
{
    protected $repository = MockRunningRepository::class;

    public function running()
    {
        $this->app->alias(MockContract::class, MockClass::class);
    }

    public function thread()
    {
        $this->app->alias(MockContract::class, MockClass::class);
    }
}