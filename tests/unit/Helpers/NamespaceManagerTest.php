<?php

use Chronos\Helpers\NamespaceManager;
use PHPUnit\Framework\TestCase;


class NamespaceManagerTest extends TestCase
{
    /**
     * @covers \Chronos\Helpers\NamespaceManager::getControllerNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getThreadNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getRepositoryNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getServiceNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getProviderNamespace
     */
    public function testNamespacesAreDefaultRooted()
    {
        // Pull the class from the IoC
        $namespaceHelper = new NamespaceManager;

        // Assert
        $this->assertEmpty($namespaceHelper->getControllerNamespace());
        $this->assertEmpty($namespaceHelper->getThreadNamespace());
        $this->assertEmpty($namespaceHelper->getRepositoryNamespace());
        $this->assertEmpty($namespaceHelper->getServiceNamespace());
        $this->assertEmpty($namespaceHelper->getProviderNamespace());

    }

    /**
     * @covers \Chronos\Helpers\NamespaceManager::getControllerNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getThreadNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getRepositoryNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getServiceNamespace
     * @covers \Chronos\Helpers\NamespaceManager::getProviderNamespace
     */
    public function testNamespacesSettable()
    {
        $namespaces = [
            'CONTROLLERS' => '\\CONTROLLERS\\ARE\\HERE\\',
            'THREADS' => '\\THREADS\\ARE\\HERE\\',
            'REPOSITORIES' => '\\REPOSITORIES\\ARE\\HERE\\',
            'SERVICES' => '\\SERVICES\\ARE\\HERE\\',
            'PROVIDERS' => '\\PROVIDERS\\ARE\\HERE\\',

        ];


        // Pull the class from the IoC
        $namespaceHelper = new NamespaceManager(
            $namespaces['CONTROLLERS'],
            $namespaces['SERVICES'],
            $namespaces['THREADS'],
            $namespaces['REPOSITORIES'],
            $namespaces['PROVIDERS']
        );

        // Assert
        $this->assertSame(rtrim($namespaces['CONTROLLERS'], '\\'), $namespaceHelper->getControllerNamespace());
        $this->assertSame(rtrim($namespaces['THREADS'], '\\'), $namespaceHelper->getThreadNamespace());
        $this->assertSame(rtrim($namespaces['REPOSITORIES'], '\\'), $namespaceHelper->getRepositoryNamespace());
        $this->assertSame(rtrim($namespaces['SERVICES'], '\\'), $namespaceHelper->getServiceNamespace());
        $this->assertSame(rtrim($namespaces['PROVIDERS'], '\\'), $namespaceHelper->getProviderNamespace());

    }
}