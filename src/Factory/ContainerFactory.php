<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 13:16:30 +0800
 */

namespace Teddy\Factory;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Teddy\Container\Container;
use Teddy\Utils\Composer;

class ContainerFactory
{
    public static function create(string $basePath)
    {
        $container = Container::getInstance();
        $container->addValue('basePath', $basePath);

        // Config
        $container->addShared('config', \Teddy\Config\Config::class)->addArgument('basePath');

        // Logger
        $container->addShared('logger', \Teddy\Logger\Manager::class);

        // Request & Response
        $container->add('request', \Teddy\Http\Request::class);
        $container->add('response', \Teddy\Http\Response::class);

        // Database
        $loader = Composer::getLoader();
        AnnotationRegistry::registerLoader([$loader, 'loadClass']);
        $container->addShared('db', \Teddy\Database\Manager::class);

        // Redis
        $container->addShared('redis', \Teddy\Redis\Manager::class);

        // Flysystem
        $container->addShared('fs', \Teddy\Flysystem\Manager::class);

        // JWT
        $container->addShared('jwt', \Teddy\Jwt\Manager::class);

        // Others
        $container->addShared('base64', \Teddy\Base64::class);
        $container->addShared('filter', \Teddy\Filter::class);
        $container->addShared('lock', \Teddy\Lock\Factory::class);
        $container->addShared('auth', \Teddy\Auth\Manager::class);

        return $container;
    }
}
