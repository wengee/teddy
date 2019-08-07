<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 14:35:40 +0800
 */

$router->get('[/]', 'IndexController:index');

$router->group(['pattern' => '/test', 'namespace' => 'Test'], function ($router) {
    $router->get('[/]', 'IndexController:index');
});

$router->group(function ($router) {
    $router->get('/lalala', 'LalalaController:test');
});
