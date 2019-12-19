<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-19 17:52:12 +0800
 */

use App\Http\Request;
use App\Http\Response;
use App\Listeners\OnStartListener;
use App\Listeners\OnWorkerStartListener;
use Teddy\App;

$app = App::create(defined('BASE_PATH') ? BASE_PATH : dirname(__DIR__));

$app->bind('request', Request::class);
$app->bind('response', Response::class);

$app->addErrorMiddleware(true, true, true);
$app->addStaticFileMiddleware(dirname(__DIR__) . '/public');

$app->addEventListeners([
    'server.onStart' => [
        OnStartListener::class,
    ],
    'server.onWorkerStart' => [
        OnWorkerStartListener::class,
    ],
]);

return $app;
