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
     * Channel specific service provider register
     * @var array
     */
    public $register = [
        EloquentServiceProvider::class
    ];

    /**
     * Channel Specific service providers
     * after registered providers are loaded.
     * @var array
     */
    public $booted = [];
}