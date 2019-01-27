<?php

use Chronos\Helpers\ArgumentVectors;
use PHPUnit\Framework\TestCase;

class ArgumentVectorResolveFromIoCTest extends TestCase
{
    /**
     * Lets make sure that when ArgumentVectors helper class
     * is resolved from the IoC that it uses the $_SERVER['argv']
     * variables as needed.
     *
     * We'll override the defaults and when the core service provider
     * shares ArgumentVectors to the IoC it will use the fake globals.
     *
     * @covers \Chronos\Helpers\ArgumentVectors::getArguments
     * @covers \Chronos\Helpers\ArgumentVectors::getController
     * @covers \Chronos\Helpers\ArgumentVectors::getMethod
     */
    public function testArgumentVectorsResolved()
    {
        // Set variables
        $controller = 'FooController';
        $method = 'barMethod';
        $consoleArgumentVector = $controller . '@' . $method;

        $_SERVER['argv'] = [
            'fooFile.php',
            $controller . '@' . $method
        ];

        $app = new \Chronos\Foundation\Application(getcwd() . '/tests/stubs');

        // Resolve our character
        $parser = $app->make(ArgumentVectors::class);

        list($fullVector) = $parser->getArguments();

        // Assert
        $this->assertNotNull($fullVector);
        $this->assertSame($consoleArgumentVector, $fullVector);
        $this->assertSame($controller, $parser->getController());
        $this->assertSame($method, $parser->getMethod());

    }

    // need tests for getService()
}