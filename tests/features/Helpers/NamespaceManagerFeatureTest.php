<?php


use Chronos\Foundation\Application;
use PHPUnit\Framework\TestCase;


class NamespaceManagerFeatureTest extends TestCase
{

    /**
     * @covers \Chronos\Helpers\NamespaceManager::getControllerNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getThreadNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getRepositoryNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getServiceNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getProviderNamespace
     */
    public function testNamespaceResolvingFromIoC()
    {
        $dir = dirname(__FILE__) . "/../../stubs/";;

        // Set up the classes
        $app = new Application($dir);

        // Make our definitions
        $namespaces = [
            'CONTROLLERS' => '\\CONTROLLERS\\ARE\\HERE\\',
            'THREADS' => '\\THREADS\\ARE\\HERE\\',
            'REPOSITORIES' => '\\REPOSITORIES\\ARE\\HERE\\',
            'SERVICES' => '\\SERVICES\\ARE\\HERE\\',
            'PROVIDERS' => '\\PROVIDERS\\ARE\\HERE\\',

        ];

        // Set our definitions in IoC
        foreach ($namespaces as $alias => $namespace) {
            $app->defineParam($alias, $namespace);
        }

        // Pull the class from the IoC
        $namespaceHelper = $app->make(\Chronos\Helpers\NamespaceManager::class);

        // Assert
        $this->assertSame(rtrim($namespaces['CONTROLLERS'], '\\'), $namespaceHelper->getControllerNamespace());
        $this->assertSame(rtrim($namespaces['THREADS'], '\\'), $namespaceHelper->getThreadNamespace());
        $this->assertSame(rtrim($namespaces['REPOSITORIES'], '\\'), $namespaceHelper->getRepositoryNamespace());
        $this->assertSame(rtrim($namespaces['SERVICES'], '\\'), $namespaceHelper->getServiceNamespace());
        $this->assertSame(rtrim($namespaces['PROVIDERS'], '\\'), $namespaceHelper->getProviderNamespace());

    }
}