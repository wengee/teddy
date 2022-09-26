<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-26 15:57:00 +0800
 */

namespace Teddy\Container;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Log\LoggerInterface;
use Slim\App as SlimApp;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\MiddlewareDispatcherInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteParserInterface;
use Slim\Interfaces\RouteResolverInterface;

class DefaultContainer
{
    public static function create(string $basePath): Container
    {
        $container = Container::getInstance();
        $container->addValue('basePath', $basePath);

        // Slim Application
        $container->addShared(ResponseFactoryInterface::class, \Teddy\Http\ResponseFactory::class)
            ->addArgument('container')
        ;
        $container->addShared(CallableResolverInterface::class, \Teddy\CallableResolver::class)
            ->addArgument('container')
        ;
        $container->addShared(RouteCollectorInterface::class, \Teddy\Routing\RouteCollector::class)
            ->addArgument(ResponseFactoryInterface::class)
            ->addArgument(CallableResolverInterface::class)
            ->addArgument(ContainerInterface::class)
            ->addArgument(InvocationStrategyInterface::class)
            ->addArgument(RouteParserInterface::class)
        ;
        $container->addShared(StreamFactoryInterface::class, \Slim\Psr7\Factory\StreamFactory::class);
        $container->addShared(UploadedFileFactoryInterface::class, \Slim\Psr7\Factory\UploadedFileFactory::class);
        $container->addShared('slim', SlimApp::class)
            ->addArgument(ResponseFactoryInterface::class)
            ->addArgument(ContainerInterface::class)
            ->addArgument(CallableResolverInterface::class)
            ->addArgument(RouteCollectorInterface::class)
            ->addArgument(RouteResolverInterface::class)
            ->addArgument(MiddlewareDispatcherInterface::class)
        ;

        // Config
        $container->addShared('config', \Teddy\Config\Config::class)->addArgument('container');

        // Crontab
        $container->addShared('crontab', \Teddy\Crontab\Crontab::class);

        // Logger
        $container->addShared(LoggerInterface::class, \Teddy\Logger\Manager::class);
        $container->addAlias('logger', LoggerInterface::class);

        // Request & Response
        $container->add(ServerRequestInterface::class, \Teddy\Http\Request::class);
        $container->add(ResponseInterface::class, \Teddy\Http\Response::class);
        $container->addAlias('request', ServerRequestInterface::class);
        $container->addAlias('response', ResponseInterface::class);

        // Database
        $container->addShared('db', \Teddy\Database\Manager::class)->addArgument('container');
        $container->addShared('modelManager', \Teddy\Model\Manager::class);

        // Redis
        $container->addShared('redis', \Teddy\Redis\Manager::class);

        // Flysystem
        $container->addShared('fs', \Teddy\Flysystem\Manager::class);

        // Others
        $container->addShared('filter', \Teddy\Filter::class);
        $container->addShared('lock', \Teddy\Lock\Factory::class);
        $container->addShared('auth', \Teddy\Auth\Manager::class);

        return $container;
    }
}
