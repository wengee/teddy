<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-13 14:52:34 +0800
 */
namespace Teddy;

use Slim\RouteGroup as SlimRouteGroup;

/**
 * A collector for Routable objects with a common middleware stack
 *
 * @package Slim
 */
class RouteGroup extends SlimRouteGroup
{
    /**
     * Route namespace prefix
     *
     * @var string $namespace
     */
    protected $namespace;

    public function __construct($pattern, $callable)
    {
        if (is_array($pattern)) {
            $this->pattern = $pattern['pattern'] ?? '';
            $this->namespace = $pattern['namespace'] ?? '';
        } else {
            $this->pattern = (string) $pattern;
        }
        $this->callable = $callable;
    }

    public function setNamespace($newNamespace)
    {
        $this->namespace = $newNamespace;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }
}
