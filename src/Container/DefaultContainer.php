<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 14:32:57 +0800
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
use Teddy\Interfaces\KernelInterface;
use Teddy\Runtime;

class DefaultContainer
{
    public static function create(string $basePath, ?string $runtime = null): Container
    {
        $container = Container::getInstance();
        $container->addValue('basePath', $basePath);
        if ($runtime) {
            $container->addValue('runtime', $runtime);
            Runtime::set($runtime);
        }

        // Console Application
        $container->addShared(KernelInterface::class, \Teddy\Console\Kernel::class);

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
        $container->addShared(LoggerInterface::class, \Teddy\Log\LogManager::class);
        $container->addAlias('logger', LoggerInterface::class);

        // Request & Response
        $container->add(ServerRequestInterface::class, \Teddy\Http\Request::class);
        $container->add(ResponseInterface::class, \Teddy\Http\Response::class);
        $container->addAlias('request', ServerRequestInterface::class);
        $container->addAlias('response', ResponseInterface::class);

        // Database
        $container->addShared('db', \Teddy\Database\DbManager::class)->addArgument('container');
        $container->addShared('modelManager', \Teddy\Model\ModelManager::class);

        // Redis
        $container->addShared('redis', \Teddy\Redis\RedisManager::class);

        // Flysystem
        $container->addShared('fs', \Teddy\Flysystem\FilesystemManager::class);

        // Others
        $container->addShared('filter', \Teddy\Filter::class);
        $container->addShared('lock', \Teddy\Lock\LockManager::class);
        $container->addShared('auth', \Teddy\Auth\AuthManager::class);
        $container->addShared('pool', \Teddy\Pool\SimplePool\PoolManager::class);

        return $container;
    }
}
