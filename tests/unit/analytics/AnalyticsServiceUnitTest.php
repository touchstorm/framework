<?php

use GuzzleHttp\Client as Guzzle;
use PHPUnit\Framework\TestCase;
use VideoAmigoSdk\Client;
use VideoAmigoSdk\Contracts\RequestMaker;
use VideoAmigoSdk\Services\Analytics\AnalyticsService;

class AnalyticsServiceUnitTest extends TestCase
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $client = new Client();
        $this->service = new AnalyticsService($client);
    }

    public function testClient()
    {
        // Make sure it's callable
        $this->assertInstanceOf(RequestMaker::class, $this->service->getClient());

        // Make sure it's a Guzzle client
        $this->assertInstanceOf(Guzzle::class, $this->service->getClient()->client);
    }
}