<?php

use Chronos\Tasks\Scheduled;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    /**
     * Test new scheduled task
     * @covers \Chronos\Tasks\Task::__construct
     * @covers \Chronos\Tasks\Task::configure
     */
    public function testScheduledTasks()
    {
        $name = 'sampleTask';
        $arguments = [
            'uses' => 'testController@method',
            'type' => 'scheduled',
            'command' => 'ls -la'
        ];

        $task = new Scheduled($name, $arguments);

        $this->assertSame($arguments['uses'], $task->getService());
        $this->assertNotEquals($arguments['command'], $task->getCommand()[0]);
        $this->assertSame($arguments['type'], $task->getType());

        // Defaults to every minute on raw Scheduled task
        $this->assertSame('* * * * *', $task->runs);

    }

    /**
     * Test simple one-off task dates
     * @covers Scheduled::everyMinute()
     * @covers Scheduled::monthly()
     * @covers Scheduled::weekly()
     * @covers Scheduled::daily()
     * @covers Scheduled::days()
     * @covers Scheduled::hourly()
     * @covers Scheduled::at()
     * @covers Scheduled::at()
     */
    public function testScheduledTasksSimpleDateTest()
    {
        $dateTests = [
            [
                'when' => function (Scheduled $task) {
                    return $task->everyMinute();
                },
                'expected' => '* * * * *'
            ],
            [
                'when' => function (Scheduled $task) {
                    return $task->monthly('1,4,9,12');
                },
                'expected' => '0 0 * 1,4,9,12 *'
            ],
            [
                'when' => function (Scheduled $task) {
                    return $task->weekly('1,2,3');
                },
                'expected' => '0 0 * * 1,2,3'
            ],
            [
                'when' => function (Scheduled $task) {
                    return $task->days(0, 1, 2, 3, 4, 5, 6, 7, 7, 7, 7, 7, 7);
                },
                'expected' => '* * * * 0,1,2,3,4,5,6,7'
            ],
            [
                'when' => function (Scheduled $task) {
                    return $task->days('1-7');
                },
                'expected' => '* * * * 1-7',
            ],
            [
                'when' => function (Scheduled $task) {
                    return $task->hourly('0-59');
                },
                'expected' => '0-59 * * * *',
            ],
            [
                'when' => function (Scheduled $task) {
                    return $task->hourly('5');
                },
                'expected' => '5 * * * *',
            ],
            [
                'when' => function (Scheduled $task) {
                    return $task->daily();
                },
                'expected' => '0 0 * * *',
            ],
            [
                'when' => function (Scheduled $task) {
                    return $task->at('09:30');
                },
                'expected' => '30 9 * * *',
            ],
            [
                'when' => function (Scheduled $task) {
                    return $task->on('2018-01-31 03:45:59');
                },
                'expected' => '45 3 31 1 *',
            ]
        ];

        foreach ($dateTests as $test) {

            $name = 'sampleTask';
            $arguments = [
                'uses' => 'testController@getMethod',
                'type' => 'scheduled'
            ];

            $task = new Scheduled($name, $arguments);

            // Base assertions
            $this->assertSame($arguments['uses'], $task->getService());
            $this->assertSame($arguments['type'], $task->getType());

            // Get closure
            $when = $test['when'];

            // Pass task through closure to test cron time
            $task = $when($task);

            if (isset($test['match'])) {

                $expected = str_replace('?', $test['match'], $test['expected']);
                $this->assertSame($expected, $task->runs);
                continue;
            }

            $this->assertSame($test['expected'], $task->runs);
        }
    }


    /**
     * Test each day of the week by name and by int
     * @covers Scheduled::weekly()
     */
    public function testScheduledTasksDaysOfWeekTest()
    {
        $days = [
            'mon' => 1,
            'tues' => 2,
            'wed' => 3,
            'thur' => 4,
            'fri' => 5,
            'sat' => 6,
            'sun' => 7,
            'monday' => 1,
            'tuesday' => 2,
            'wednesday' => 3,
            'thursday' => 4,
            'friday' => 5,
            'saturday' => 6,
            'sunday' => 7,
        ];

        // Test days by string name
        foreach ($days as $day => $expression) {

            $name = 'sampleTask';
            $arguments = [
                'uses' => 'testController@getMethod',
                'type' => 'scheduled'
            ];

            $task = new Scheduled($name, $arguments);

            $task = $task->weekly($day);

            $this->assertSame('0 0 * * ' . $expression, $task->runs);
        }

        // Test days of week by int
        for ($day = 0; $day < 7; $day++) {
            $name = 'sampleTask';
            $arguments = [
                'uses' => 'testController@getMethod',
                'type' => 'scheduled'
            ];

            $task = new Scheduled($name, $arguments);

            $task = $task->weekly($day);

            $this->assertSame('0 0 * * ' . $day, $task->runs);
        }
    }

    /**
     * Test each day of the month and every hour of the day
     * @covers Scheduled::monthly()
     * @covers Scheduled::at()
     */
    public function testScheduledTasksDaysOfMonthTest()
    {
        for ($day = 1; $day < 31; $day++) {

            for ($hour = 0; $hour < 23; $hour++) {

                $name = 'sampleTask';
                $arguments = [
                    'uses' => 'testController@getMethod',
                    'type' => 'scheduled'
                ];

                $task = new Scheduled($name, $arguments);

                $task = $task->monthly($day)->at($hour . ":00");

                $this->assertSame('0 ' . $hour . ' * ' . $day . ' *', $task->runs);
            }
        }
    }

    /**
     * Test the yearly coverage
     * @covers Scheduled::yearly()
     */
    public function testScheduledTasksMonthOfYearTest()
    {
        for ($year = 1; $year < 12; $year++) {

            $name = 'sampleTask';
            $arguments = [
                'uses' => 'testController@getMethod',
                'type' => 'scheduled'
            ];

            $task = new Scheduled($name, $arguments);

            $task = $task->yearly($year);

            $this->assertSame('0 0 1 ' . $year . ' *', $task->runs);
        }
    }

    /**
     * Test the on a specific day yearly
     * @covers Scheduled::on()
     */
    public function testScheduledTasksRandomDaysTest()
    {
        $dates = [
            '2018-01-28 23:45:00' => '45 23 28 1 *',
            '2018-02-27 22:44:00' => '44 22 27 2 *',
            '2018-03-26 21:43:00' => '43 21 26 3 *',
            '2018-04-25 20:42:00' => '42 20 25 4 *',
            '2018-05-24 19:41:00' => '41 19 24 5 *',
            '2018-06-23 18:40:00' => '40 18 23 6 *',
            '2018-07-22 17:39:00' => '39 17 22 7 *',
            '2018-08-21 16:38:00' => '38 16 21 8 *',
            '2018-09-20 15:37:00' => '37 15 20 9 *',
            '2018-10-19 14:36:00' => '36 14 19 10 *',
            '2018-11-18 13:35:00' => '35 13 18 11 *',
            '2018-12-17 12:34:00' => '34 12 17 12 *',
        ];

        foreach ($dates as $date => $expression) {

            $name = 'sampleTask';
            $arguments = [
                'uses' => 'testController@getMethod',
                'type' => 'scheduled'
            ];

            $task = new Scheduled($name, $arguments);

            $task = $task->on($date);

            $this->assertSame($expression, $task->runs);
        }
    }
}