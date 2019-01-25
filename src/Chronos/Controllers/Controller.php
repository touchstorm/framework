<?php

namespace Chronos\Controllers;

class Controller
{
    /**
     * Channel specific service provider register
     * @var array
     */
    public $register = [
        \Chronos\Providers\EloquentServiceProvider::class
    ];

    /**
     * Channel Specific service providers
     * after registered providers are loaded.
     * @var array
     */
    public $booted = [];
}