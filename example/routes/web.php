<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-31 16:54:15 +0800
 */

use Teddy\Routing\RouteCollectorProxy;

/** @var RouteCollectorProxy $router */
$router->map(['GET', 'POST'], '[/]', 'IndexController:index');
$router->post('/upload', 'IndexController:upload');
