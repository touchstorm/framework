<?php

use Chronos\Controllers\Controller;

class MockController extends Controller
{
    public $providers = [
        MockServiceProvider::class
    ];

    public function someMethod($runFoo = 'baz')
    {
        return $runFoo;
    }

    public function foo($fooValue = 'baz')
    {
        return $fooValue;
    }

}