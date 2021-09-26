<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-26 17:16:16 +0800
 */

namespace Teddy\Container;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\RouteCollectorInterface;

class DefaultContainer
{
    public static function create(string $basePath): Container
    {
        $container = Container::getInstance();
        $container->addValue('basePath', $basePath);

        // Slim Application
        $container->addShared(ResponseFactoryInterface::class, \Teddy\Http\ResponseFactory::class);
        $container->addShared(CallableResolverInterface::class, \Teddy\CallableResolver::class);
        $container->addShared(RouteCollectorInterface::class, \Teddy\Routing\RouteCollector::class);
        $container->addShared('slim', \Teddy\SlimApp::class);

        // Config
        $container->addShared('config', \Teddy\Config\Config::class);

        // Logger
        $container->addShared(LoggerInterface::class, \Teddy\Logger\Manager::class);
        $container->addAlias('logger', LoggerInterface::class);

        // Request & Response
        $container->add(ServerRequestInterface::class, \Teddy\Http\Request::class);
        $container->add(ResponseInterface::class, \Teddy\Http\Response::class);
        $container->addAlias('request', ServerRequestInterface::class);
        $container->addAlias('response', ResponseInterface::class);

        // Database
        AnnotationRegistry::registerUniqueLoader('class_exists');
        $container->addShared('db', \Teddy\Database\Manager::class);

        // Redis
        $container->addShared('redis', \Teddy\Redis\Manager::class);

        // Flysystem
        $container->addShared('fs', \Teddy\Flysystem\Manager::class);

        // Others
        $container->addShared('base64', \Teddy\Base64::class);
        $container->addShared('filter', \Teddy\Filter::class);
        $container->addShared('lock', \Teddy\Lock\Factory::class);
        $container->addShared('auth', \Teddy\Auth\Manager::class);

        return $container;
    }
}
