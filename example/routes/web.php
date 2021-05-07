<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-07 10:56:16 +0800
 */

use App\Middlewares\Bar;
use App\Middlewares\Foo;
use App\Middlewares\Test;
use Teddy\Routing\RouteCollectorProxy;

/** @var RouteCollectorProxy $router */
$router->map(['GET', 'POST'], '[/]', 'IndexController:index');
$router->post('/upload', 'IndexController:upload');

$router->group('/test', function (RouteCollectorProxy $router): void {
    $router->group('/foo', function (RouteCollectorProxy $router): void {
        $router->get('[/]', 'IndexController:index');
    })->add(new Foo());

    $router->get('[/]', 'IndexController:index');

    $router->group('/bar', function (RouteCollectorProxy $router): void {
        $router->get('[/]', 'IndexController:index');
    })->add(new Bar());
})->add(new Test());

$router->group('/foo', function (RouteCollectorProxy $router): void {
    $router->get('[/]', 'IndexController:index');
})->add(new Foo());

$router->group('/bar', function (RouteCollectorProxy $router): void {
    $router->get('[/]', 'IndexController:index');
})->add(new Bar());
