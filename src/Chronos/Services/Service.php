<?php

namespace Chronos\Services;

use Chronos\Foundation\Application;
use Chronos\Repositories\Contracts\QueueRepositoryContract;

class Service
{
    /**
     * Application container
     * @var Application $app
     */
    protected $app;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * Service constructor.
     * @param Application $app
     * @param string $namespace
     * @throws \Auryn\ConfigException
     */
    public function __construct(Application $app,string $namespace)
    {
        $this->app = $app;
        $this->bindQueueRepository();
        $this->namespace = $namespace;
    }

    /**
     * @return void
     * @throws \Auryn\ConfigException
     * @throws ThreadedServiceException
     */
    protected function bindQueueRepository()
    {
        if (!isset($this->repository)) {
            throw new ThreadedServiceException('Repository not found. Please add a repository attribute to your thread getService.', 100);
        }

        $this->app->alias(QueueRepositoryContract::class, $this->repository);
    }
}