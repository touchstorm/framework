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
     */
    public function testArgumentVectorsResolved()
    {
        // Set variables
        $controller = 'FooController';
        $method = 'barMethod';
        $consoleArgumentVector = $controller . '@' . $method;

        $_SERVER['argv'] = [
            'scheduled.php',
            $controller . '@' . $method
        ];

        $app = new \Chronos\Foundation\Application(getcwd() . '/tests/stubs');

        // Resolve our character
        $parser = $app->make(ArgumentVectors::class);

        list($fullVector) = $parser->getArguments();

        // Assert
        $this->assertNotNull($fullVector);
        $this->assertSame($consoleArgumentVector, $fullVector);
    }

    /**
     * @covers \Chronos\Helpers\ArgumentVectors::type('scheduled')
     * @covers \Chronos\Helpers\ArgumentVectors::controller
     * @covers \Chronos\Helpers\ArgumentVectors::scheduled
     * @throws \Auryn\InjectionException
     * @throws \Chronos\Exceptions\ArgumentVectorException
     */
    public function testScheduledArgumentVectorsResolved()
    {
        // Set variables
        $controller = 'FooController';
        $method = 'barMethod';

        $_SERVER['argv'] = [
            'scheduled.php',
            $controller . '@' . $method
        ];

        $app = new \Chronos\Foundation\Application(getcwd() . '/tests/stubs');

        // Resolve our character
        $parser = $app->make(ArgumentVectors::class);

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
     * @throws \Auryn\InjectionException
     * @throws \Chronos\Exceptions\ArgumentVectorException
     */
    public function testRunningArgumentVectorsResolved()
    {
        // Set variables
        $service = 'FooService';

        $_SERVER['argv'] = [
            'running.php',
            $service
        ];

        $app = new \Chronos\Foundation\Application(getcwd() . '/tests/stubs');

        // Resolve our character
        $parser = $app->make(ArgumentVectors::class);

        // Assert
        $this->assertSame($service, $parser->type('running')->service());
        $this->assertSame($service, $parser->running()->service());

    }

    // need tests for various kernels()
}