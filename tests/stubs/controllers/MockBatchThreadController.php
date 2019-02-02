<?php

use Chronos\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;

class MockBatchThreadController extends Controller
{
    public $providers = [
        MockServiceProvider::class
    ];

    /**
     * Batch of Queue models that will be processed
     * @var Collection $batch
     */
    protected $batch;

    public function __construct(Collection $batch)
    {
        $this->batch = $batch;
    }

    public function someMethod($runFoo = 'baz')
    {
        return $runFoo;
    }

    public function foo($fooValue = 'baz')
    {
        return $fooValue;
    }

    public function handle()
    {
        return print_r($this->batch->pluck('id')->toArray(), true);
    }

}