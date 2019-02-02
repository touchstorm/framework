<?php

use Chronos\Exceptions\ArgumentVectorException;
use Chronos\Helpers\ArgumentVectors;
use PHPUnit\Framework\TestCase;


class ArgumentVectorTest extends TestCase
{
    /**
     * @covers \Chronos\Helpers\ArgumentVectors::getArguments
     * @covers \Chronos\Helpers\ArgumentVectors::getArgument
     */
    public function testArgumentVectorsOnScheduledTask()
    {
        $controller = 'MockController';
        $method = 'foo';
        $argument = $controller . '@' . $method;

        $parser = new ArgumentVectors([
            'scheduled.php',
            $argument
        ]);

        $this->assertSame($argument, $parser->getArguments()[0]);
        $this->assertSame($argument, $parser->getArgument(0));
        $this->assertNull($parser->getArgument(1)); // only 1 item in this array, there should be no over setting
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::type('scheduled')
     * @covers \Chronos\Helpers\ArgumentVectors::getController
     * @covers \Chronos\Helpers\ArgumentVectors::scheduled
     * @covers \Chronos\Helpers\ArgumentVectors::forScheduled
     * @throws ArgumentVectorException
     */
    public function testScheduledArgumentVectorsResolved()
    {
        // Set variables
        $controller = 'MockController';
        $method = 'foo';
        $argument = $controller . '@' . $method;

        $parser = new ArgumentVectors([
            'scheduled.php',
            $argument
        ]);


        $args = $parser->forScheduled();

        // Assert
        // long form
        $this->assertSame($controller, $parser->type('scheduled')->getController());
        $this->assertSame($method, $parser->type('scheduled')->getMethod());

        // Shorthand
        $this->assertSame($controller, $args->getController());
        $this->assertSame($method, $args->getMethod());

    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::type('running')
     * @covers \Chronos\Helpers\ArgumentVectors::running
     * @covers \Chronos\Helpers\ArgumentVectors::getController
     * @covers \Chronos\Helpers\ArgumentVectors::forRunning
     * @throws ArgumentVectorException
     */
    public function testRunningArgumentVectorsResolved()
    {
        // Set variables
        $service = 'MockRunningService';

        $parser = new ArgumentVectors([
            'running.php',
            $service
        ]);

        $args = $parser->forRunning();

        // Assert
        $this->assertSame($service, $parser->type('running')->getService());
        $this->assertSame($service, $args->getService());
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::type('runningThread')
     * @covers \Chronos\Helpers\ArgumentVectors::getQueueId
     * @covers \Chronos\Helpers\ArgumentVectors::getService
     * @covers \Chronos\Helpers\ArgumentVectors::forRunningThread
     * @throws ArgumentVectorException
     */
    public function testRunningThreadArgumentVectorsResolved()
    {
        // Set variables
        $id = 1;
        $service = 'MockRunningService';

        $parser = new ArgumentVectors([
            'thread.php',
            $id,
            $service
        ]);

        $args = $parser->forRunningThread();

        // Assert
        $this->assertSame($service, $parser->type('runningThread')->getService());
        $this->assertSame($id, $args->getQueueId());
        $this->assertSame($service, $args->getService());
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::type('batchThread')
     * @covers \Chronos\Helpers\ArgumentVectors::getQueueId
     * @covers \Chronos\Helpers\ArgumentVectors::getService
     * @covers \Chronos\Helpers\ArgumentVectors::forRunningThread
     * @throws ArgumentVectorException
     */
    public function testBatchThreadArgumentVectorsResolved()
    {
        // Set variables
        $id = '1~2~3~4';
        $service = 'MockBatchService';

        $parser = new ArgumentVectors([
            'batchThread.php',
            $id,
            $service
        ]);

        $args = $parser->forBatchThread();

        // Assert
        $this->assertSame($service, $parser->type('batchThread')->getService());
        $this->assertSame($id, $args->getBatchQueueId());
        $this->assertSame($service, $args->getService());
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::type('running')
     * @covers \Chronos\Helpers\ArgumentVectors::running
     * @covers \Chronos\Helpers\ArgumentVectors::getController
     * @throws ArgumentVectorException
     */
    public function testBatchArgumentVectorsResolved()
    {
        // Set variables
        $service = 'MockBatchService';

        $parser = new ArgumentVectors([
            'batch.php',
            $service
        ]);

        $args = $parser->forBatch();

        // Assert
        $this->assertSame($service, $parser->type('batch')->getService());
        $this->assertSame($service, $args->getService());
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::parseScheduledArguments
     * @throws ArgumentVectorException
     */
    public function testScheduledMismatchException()
    {
        $controller = 'MockController';
        $method = 'foo';
        $argument = $controller . '@' . $method;

        $parser = new ArgumentVectors([
            'foo.php',
            $argument
        ]);

        $this->expectExceptionCode(422);
        $this->expectException(ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->forScheduled()->getController();
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::parseRunningArguments
     * @throws ArgumentVectorException
     */
    public function testRunningMismatchException()
    {
        $controller = 'MockController';
        $method = 'foo';
        $argument = $controller . '@' . $method;

        $parser = new ArgumentVectors([
            'foo.php',
            $argument
        ]);

        $this->expectExceptionCode(422);
        $this->expectException(ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->forRunning()->getService();

    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::parseRunningThreadArguments
     * @throws ArgumentVectorException
     */
    public function testRunningThreadMismatchException()
    {
        $controller = 'MockController';
        $method = 'foo';
        $argument = $controller . '@' . $method;

        $parser = new ArgumentVectors([
            'foo.php',
            $argument
        ]);

        $this->expectExceptionCode(422);
        $this->expectException(ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->forRunningThread()->getService();
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::parseBatchArguments
     * @throws ArgumentVectorException
     */
    public function testBatchMismatchException()
    {
        $controller = 'MockController';
        $method = 'foo';
        $argument = $controller . '@' . $method;

        $parser = new ArgumentVectors([
            'foo.php',
            $argument
        ]);

        $this->expectExceptionCode(422);
        $this->expectException(ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->forBatch()->getQueueId();
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::parseBatchThreadArguments
     * @throws ArgumentVectorException
     */
    public function testBatchThreadMismatchException()
    {
        $controller = 'MockController';
        $method = 'foo';
        $argument = $controller . '@' . $method;

        $parser = new ArgumentVectors([
            'foo.php',
            $argument
        ]);

        $this->expectExceptionCode(422);
        $this->expectException(ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->forBatchThread()->getService();
    }

}