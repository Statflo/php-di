<?php

namespace Statflo\DI;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class Bootstrap
{
    private $container;

    static $bootstrap;

    private function __construct()
    {
        $this->container = new ContainerBuilder();
    }

    public static function run(array $configuration = [])
    {
        if (self::$bootstrap) {
            return self::$bootstrap;
        }

        $bootstrap  = new self();
        $parameters = isset($configuration['parameters']) ? $configuration['parameters'] : [];

        $bootstrap->defineParameters($parameters);

        $bootstrap->defineSession($configuration);
        $loader = new XmlFileLoader($bootstrap->getContainer(), new FileLocator($configuration['config_path']));
        $loader->load('config.xml');

        self::$bootstrap = $bootstrap;

        return self::$bootstrap;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function get($service)
    {
        if (!$this->container->has($service)) {
            return null;
        }

        return $this
            ->container
            ->get($service)
        ;
    }

    private function defineParameters(array $parameters = [])
    {
        foreach ($parameters as $parameter => $value) {
            $this
                ->container
                ->setParameter($parameter, $value)
            ;
        }
    }

    private function define($serviceName, $className, array $configuration = [])
    {
        $this
            ->container
            ->setDefinition($serviceName, $this->getDefinition($className, $configuration));
    }

    private function defineFactory(
        $serviceName,
        $className,
        $factoryName,
        $factoryMethod,
        array $configuration = []
    ) {
        $definition = $this->getDefinition($className, $configuration);

        $definition->setFactory([$factoryName, $factoryMethod]);
        $this
            ->container
            ->setDefinition($serviceName, $definition);
    }

    private function getDefinition($className, array $configuration = [])
    {
        $definition = new Definition(
            $className,
            $configuration
        );

        $definition->setLazy(true);

        return $definition;
    }

    private function defineSession(array $configuration = [])
    {
        if (!isset($configuration['session'])) {
            return;
        }

        $this->define(
            'statflo.session',
            \Statflo\DI\DTO\Collection::class,
            $configuration['session']
        );

        $auth = [];

        if (isset($configuration['session']['authaccount'])) {
            $auth = [$configuration['session']['authaccount']];
        }

        $this->define(
            'statflo.auth',
            \Statflo\DI\DTO\Auth::class,
            $auth
        );
    }
}
