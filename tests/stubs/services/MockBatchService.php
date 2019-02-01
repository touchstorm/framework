<?php

use Chronos\Services\ThreadedService;

class MockBatchService extends ThreadedService
{
    protected $repository = MockBatchRepository::class;

    public function running()
    {
        $this->app->alias(MockContract::class, MockClass::class);
    }

    public function thread()
    {
        $this->app->alias(MockContract::class, MockClass::class);
    }
}