<?php

use PHPUnit\Framework\TestCase;


class ArgumentVectorTest extends TestCase
{
    /**
     * @covers \Chronos\Helpers\ArgumentVectors::getArguments
     * @covers \Chronos\Helpers\ArgumentVectors::getArgument
     */
    public function testArgumentVectorsOnScheduledTask()
    {
        $controller = 'SomeController';
        $method = 'someMethod';
        $argument = $controller . '@' . $method;

        $parser = new \Chronos\Helpers\ArgumentVectors([
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
     * @throws \Chronos\Exceptions\ArgumentVectorException
     */
    public function testScheduledArgumentVectorsResolved()
    {
        // Set variables
        $controller = 'FooController';
        $method = 'barMethod';
        $argument = $controller . '@' . $method;

        $parser = new \Chronos\Helpers\ArgumentVectors([
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
     * @throws \Chronos\Exceptions\ArgumentVectorException
     */
    public function testRunningArgumentVectorsResolved()
    {
        // Set variables
        $service = 'FooService';

        $parser = new \Chronos\Helpers\ArgumentVectors([
            'running.php',
            $service
        ]);

        // Assert
        $this->assertSame($service, $parser->type('running')->service());
        $this->assertSame($service, $parser->running()->service());
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::parseScheduledArguments
     * @throws \Chronos\Exceptions\ArgumentVectorException
     */
    public function testScheduledMismatchException()
    {
        $controller = 'SomeController';
        $method = 'someMethod';
        $argument = $controller . '@' . $method;

        $parser = new \Chronos\Helpers\ArgumentVectors([
            'foo.php',
            $argument
        ]);

        $this->expectExceptionCode(500);
        $this->expectException(\Chronos\Exceptions\ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->scheduled()->controller();
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::parseRunningArguments
     * @throws \Chronos\Exceptions\ArgumentVectorException
     */
    public function testRunningMismatchException()
    {
        $controller = 'SomeController';
        $method = 'someMethod';
        $argument = $controller . '@' . $method;

        $parser = new \Chronos\Helpers\ArgumentVectors([
            'foo.php',
            $argument
        ]);

        $this->expectExceptionCode(500);
        $this->expectException(\Chronos\Exceptions\ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->running()->service();

    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::parseRunningThreadArguments
     * @throws \Chronos\Exceptions\ArgumentVectorException
     */
    public function testRunningThreadMismatchException()
    {
        $controller = 'SomeController';
        $method = 'someMethod';
        $argument = $controller . '@' . $method;

        $parser = new \Chronos\Helpers\ArgumentVectors([
            'foo.php',
            $argument
        ]);

        $this->expectExceptionCode(500);
        $this->expectException(\Chronos\Exceptions\ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->runningThread()->service();
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::parseBatchArguments
     * @throws \Chronos\Exceptions\ArgumentVectorException
     */
    public function testBatchMismatchException()
    {
        $controller = 'SomeController';
        $method = 'someMethod';
        $argument = $controller . '@' . $method;

        $parser = new \Chronos\Helpers\ArgumentVectors([
            'foo.php',
            $argument
        ]);

        $this->expectExceptionCode(500);
        $this->expectException(\Chronos\Exceptions\ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->batch()->queueId();
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::parseBatchThreadArguments
     * @throws \Chronos\Exceptions\ArgumentVectorException
     */
    public function testBatchThreadMismatchException()
    {
        $controller = 'SomeController';
        $method = 'someMethod';
        $argument = $controller . '@' . $method;

        $parser = new \Chronos\Helpers\ArgumentVectors([
            'foo.php',
            $argument
        ]);

        $this->expectExceptionCode(500);
        $this->expectException(\Chronos\Exceptions\ArgumentVectorException::class);
        $this->expectExceptionMessage('Dispatch argument vector mismatch');

        // Trigger
        $parser->batchThread()->service();
    }

}