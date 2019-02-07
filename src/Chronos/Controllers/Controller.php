<?php

namespace Chronos\Controllers;

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
    public $register = [];

    /**
     * Channel Specific getService providers
     * after registered providers are loaded.
     * @var array
     */
    public $booted = [];
}