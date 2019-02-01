<?php

namespace Chronos\Dispatchers\Contracts;

/**
 * Interface DispatcherContract
 * @package Chronos\Dispatchers\Contracts
 */
interface DispatcherContract
{
    /**
     * @param array $options
     * @return mixed
     */
    public function handle(array $options);
}