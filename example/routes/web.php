<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-09-02 16:52:20 +0800
 */

use App\Middlewares\Bar;
use App\Middlewares\Foo;
use App\Middlewares\Test;

$router->get('[/]', 'IndexController:index');

$router->group('/test', function ($router): void {
    $router->group('/foo', function ($router): void {
        $router->get('[/]', 'IndexController:index');
    })->add(new Foo);

    $router->get('[/]', 'IndexController:index');

    $router->group('/bar', function ($router): void {
        $router->get('[/]', 'IndexController:index');
    })->add(new Bar);
})->add(new Test);

$router->group('/foo', function ($router): void {
    $router->get('[/]', 'IndexController:index');
})->add(new Foo);

$router->group('/bar', function ($router): void {
    $router->get('[/]', 'IndexController:index');
})->add(new Bar);
