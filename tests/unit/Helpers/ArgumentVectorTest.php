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
     * @covers \Chronos\Helpers\ArgumentVectors::controller
     * @covers \Chronos\Helpers\ArgumentVectors::scheduled
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

        // Assert
        $this->assertSame($controller, $parser->type('scheduled')->controller());
        $this->assertSame($method, $parser->type('scheduled')->method());
        $this->assertSame($controller, $parser->scheduled()->controller());
        $this->assertSame($method, $parser->scheduled()->method());

    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::type('running')
     * @covers \Chronos\Helpers\ArgumentVectors::running
     * @covers \Chronos\Helpers\ArgumentVectors::controller
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

        // Assert
        $this->assertSame($service, $parser->type('running')->service());
        $this->assertSame($service, $parser->running()->service());
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

        $this->expectExceptionCode(500);
        $this->expectException(ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->scheduled()->controller();
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

        $this->expectExceptionCode(500);
        $this->expectException(ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->running()->service();

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

        $this->expectExceptionCode(500);
        $this->expectException(ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->runningThread()->service();
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

        $this->expectExceptionCode(500);
        $this->expectException(ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->batch()->queueId();
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

        $this->expectExceptionCode(500);
        $this->expectException(ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->batchThread()->service();
    }

}