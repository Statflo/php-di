<?php

namespace Statflo\DI;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

class Bootstrap
{
    private $container;

    static $bootstrap;

    private function __construct($container)
    {
        $this->container = $container;
    }

    public static function run(array $configuration = [])
    {
        if (self::$bootstrap) {
            return self::$bootstrap;
        }

        $parameters = isset($configuration['parameters']) ? $configuration['parameters'] : [];
        $cachePath  = getenv('CONTAINER_CACHE');

        if ($cachePath && file_exists($cachePath)) {
            require_once $cachePath;

            $container = new \ProjectServiceContainer();
            $bootstrap = new self($container);

            self::$bootstrap = $bootstrap;

            return self::$bootstrap;
        }

        $container = new ContainerBuilder();
        $bootstrap = new self($container);

        $bootstrap->defineParameters($parameters);

        $loader = new XmlFileLoader($container, new FileLocator($configuration['config_path']));
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

    public function compile()
    {
        if ($this->container->isFrozen()) {
            return;
        }

        $cachePath = getenv('CONTAINER_CACHE');

        $this
            ->container
            ->compile()
        ;

        if ($cachePath) {
            $dumper = new PhpDumper($this->container);
            file_put_contents($cachePath, $dumper->dump());
        }
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

    public function define($serviceName, $className, array $configuration = [])
    {
        if ($this->container->isFrozen()) {
            return;
        }

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
}
