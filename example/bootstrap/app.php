<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:49:22 +0800
 */

use App\Http\Request;
use App\Http\Response;
use Teddy\Application;

$app = Application::create(defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__));

$app->getContainer()->add('request', Request::class);
$app->getContainer()->add('response', Response::class);

$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware([]);
$app->addErrorMiddleware(true, true, true);
$app->addStaticFileMiddleware(dirname(__DIR__).'/public');
$app->addCorsMiddleware();

return $app;
