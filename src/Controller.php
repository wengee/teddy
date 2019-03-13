<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-10 14:12:59 +0800
 */
namespace Teddy;

use Interop\Container\ContainerInterface;

abstract class Controller
{
    protected $container;

    final public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    /**
     * Bridge container get.
     *
     * @param string $name
     */
    final public function __get($name)
    {
        return $this->container->get($name);
    }

    /**
     * Bridge container has.
     *
     * @param string $name
     */
    final public function __isset($name)
    {
        return $this->container->has($name);
    }
}
