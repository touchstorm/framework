<?php

namespace Chronos\Helpers;

class NamespaceManager
{
    protected $controllers;
    protected $services;
    protected $threads;
    protected $repositories;
    protected $providers;

    /**
     * NamespaceManager constructor.
     * @param $CONTROLLERS
     * @param $SERVICES
     * @param $THREADS
     * @param $REPOSITORIES
     * @param $PROVIDERS
     */
    public function __construct($CONTROLLERS = '\\', $SERVICES = '\\', $THREADS = '\\', $REPOSITORIES = '\\', $PROVIDERS = '\\')
    {
        $this->controllers = $CONTROLLERS;
        $this->services = $SERVICES;
        $this->threads = $THREADS;
        $this->repositories = $REPOSITORIES;
        $this->providers = $PROVIDERS;
    }

    /**
     * @return string
     */
    public function getControllerNamespace()
    {
        return rtrim($this->controllers, '\\');
    }

    /**
     * @return string
     */
    public function getServiceNamespace()
    {
        return rtrim($this->services, '\\');
    }

    /**
     * @return string
     */
    public function getThreadNamespace()
    {
        return rtrim($this->threads, '\\');
    }

    /**
     * @return string
     */
    public function getRepositoryNamespace()
    {
        return rtrim($this->repositories, '\\');
    }

    /**
     * @return string
     */
    public function getProviderNamespace()
    {
        return rtrim($this->providers, '\\');
    }

}