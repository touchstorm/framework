<?php

use PHPUnit\Framework\TestCase;

class ScheduledTaskTest extends TestCase
{
    protected $service;

    public function __construct()
    {
        parent::__construct();
        $client = new Client();
        $this->service = new AnalyticsService($client);
    }

    public function testDemographics()
    {
        // Set parts
        $parts = ['demographics', 'demographicsLife'];

        // Set parameters
        $params =[
            'channelId' => 47,
            'fromDate' => '2017-09-10',
            'toDate' => '2017-09-12'
        ];

        // Call services
        $response = $this->service->channel($parts, $params);

        // Assert full response collection
        $this->assertInstanceOf(Collection::class, $response);

        // Loop through and assert each responses as a collections
        foreach ($parts as $part) {
            $this->assertInstanceOf(Collection::class, $response->get($part));
        }
    }

    public function testDemographicsFilters()
    {
        // Set parts
        $parts = ['demographics', 'demographicsLife'];
        $genders = ['m', 'f'];

        foreach ($genders as $gender) {

            $params =[
                'channelId' => 47,
                'fromDate' => '2017-09-10',
                'toDate' => '2017-09-12',
                'gender' => $gender
            ];

            // Call services
            $response = $this->service->channel($parts, $params);

            // Assert full response collection
            $this->assertInstanceOf(Collection::class, $response);

            // Loop through and make assertions
            foreach ($parts as $part) {
                $this->assertInstanceOf(Collection::class, $response->get($part));
                $this->assertArrayHasKey($gender, $response->get($part)->pluck('gender')->flip());
            }
        }
    }

    public function testVideoDemographics()
    {
        // Set parts
        $parts = ['demographics', 'demographicsLife'];

        // Call services
        $response = $this->service->videos($parts, ['videoId' => 12458055536, 'fromDate' => '2017-08-13', 'toDate' => '2017-08-14']);

        // Assert full response collection
        $this->assertInstanceOf(Collection::class, $response);

        $videos = $response->get('videos');

        foreach($videos as $video) {

            foreach ($parts as $part) {
                $this->assertInstanceOf(Collection::class, $video->get('parts')->get($part));
            }
        }
    }

    public function testVideoDemographicsFilters()
    {
        // Set parts
        $parts = ['demographics', 'demographicsLife'];
        $genders = ['m', 'f'];

        // Loop through genders
        foreach ($genders as $gender) {

            $params =[
                'videoId' => 12458055536,
                'fromDate' => '2017-09-10',
                'toDate' => '2017-09-12',
                'gender' => $gender
            ];

            // Call services
            $response = $this->service->videos($parts, $params);

            // Assert full response collection
            $this->assertInstanceOf(Collection::class, $response);

            $videos = $response->get('videos');

            foreach($videos as $video) {

                foreach ($parts as $part) {
                    $this->assertInstanceOf(Collection::class, $video->get('parts')->get($part));
                    $this->assertArrayHasKey($gender, $video->get('parts')->get($part)->pluck('gender')->flip());
                }
            }
        }
    }

}