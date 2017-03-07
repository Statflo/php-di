<?php
namespace Statflo\DI\Base;

use Statflo\DI\Bootstrap;

abstract class ContainerAware
{
    private $container;

    public final function __construct(Bootstrap $bootstrap)
    {
        $this->container = $bootstrap;
    }

    protected final function get($key)
    {
        return $this->container->get($key);
    }
}
