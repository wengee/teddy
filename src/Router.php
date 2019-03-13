<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-13 17:53:41 +0800
 */
namespace Teddy;

use Slim\Router as SlimRouter;

class Router extends SlimRouter
{
    public function map($methods, $pattern, $handler)
    {
        if (is_string($handler) && !is_callable($handler)) {
            $handler = $this->processNamespace($handler);
        }

        return parent::map($methods, $pattern, $handler);
    }

    public function pushGroup($pattern, $callable)
    {
        $group = new RouteGroup($pattern, $callable);
        array_push($this->routeGroups, $group);
        return $group;
    }

    protected function processNamespace(string $handler)
    {
        if ($handler{0} === '\\') {
            return $handler;
        }

        $prefix = '';
        foreach ($this->routeGroups as $group) {
            $namespace = $group->getNamespace();
            if (empty($namespace)) {
                continue;
            }

            $namespace = \str_finish($namespace, '\\');
            if ($namespace{0} === '\\') {
                $prefix = $namespace;
            } else {
                $prefix .= $namespace;
            }
        }

        if ($prefix) {
            $handler = \str_finish(\str_start($prefix, '\\'), '\\') . $handler;
        }

        return $handler;
    }
}
