<?php

namespace Chronos\Controllers;

use Chronos\Providers\EloquentServiceProvider;

/**
 * Class Controller
 * @package Chronos\Controllers
 */
class Controller
{
    /**
     * Channel specific getService provider register
     * @var array
     */
    public $register = [
        EloquentServiceProvider::class
    ];

    /**
     * Channel Specific getService providers
     * after registered providers are loaded.
     * @var array
     */
    public $booted = [];
}