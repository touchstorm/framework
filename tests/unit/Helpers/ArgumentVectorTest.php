<?php

use PHPUnit\Framework\TestCase;


class ArgumentVectorTest extends TestCase
{
    /**
     * @covers \Chronos\Helpers\ArgumentVectors::getArguments
     * @covers \Chronos\Helpers\ArgumentVectors::getController
     * @covers \Chronos\Helpers\ArgumentVectors::getMethod
     */
    public function testArgumentVectorsOnScheduledTask()
    {
        $controller = 'SomeController';
        $method = 'someMethod';
        $argument = $controller . '@' . $method;

        $parser = new \Chronos\Helpers\ArgumentVectors([
            'someFile.php',
            $argument
        ]);

        $this->assertSame($argument, $parser->getArguments()[0]);
        $this->assertSame($controller, $parser->getController());
        $this->assertSame($method, $parser->getMethod());

    }

    // need tests for getService()
}