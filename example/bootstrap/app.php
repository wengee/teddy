<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 21:06:50 +0800
 */

use App\Http\Request;
use App\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teddy\Application;
use Teddy\Container\DefaultContainer;
use Teddy\Runtime;

$container = DefaultContainer::create(defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__), Runtime::SWOOLE);
$container->add(ServerRequestInterface::class, Request::class);
$container->add(ResponseInterface::class, Response::class);

$app = Application::create($container);
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware([]);
$app->addErrorMiddleware(true, true, true);
$app->addStaticFileMiddleware(dirname(__DIR__).'/public');
$app->addCorsMiddleware();

return $app;
