<?php

namespace Chronos\Controllers;

/**
 * Class Controller
 * @package Chronos\Controllers
 */
class Controller
{
    /**
     * Register the controller's service providers
     * (pre-boot)
     * @var array
     */
    public $providers = [];

    /**
     * Register the controller's service providers
     * after registered providers are loaded.
     * (booted)
     * @var array
     */
    public $booted = [];
}