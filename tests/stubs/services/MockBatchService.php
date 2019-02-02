<?php

use Chronos\Services\BatchThreadedService;

class MockBatchService extends BatchThreadedService
{
    protected $providers = [
        'batch' => [
            MockServiceProvider::class
        ],
        'thread' => [
            MockServiceProvider::class
        ]
    ];

    protected $repository = '\\MockBatchRepository';

    public function batch()
    {
        $this->app->alias(MockContract::class, MockClass::class);
    }

    public function thread()
    {
        $this->app->alias(MockContract::class, MockClass::class);
    }
}