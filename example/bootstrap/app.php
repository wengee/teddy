<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 16:05:30 +0800
 */

use App\Http\Request;
use App\Http\Response;
use App\Listeners\OnStartListener;
use App\Listeners\OnWorkerStartListener;
use Teddy\App;

$app = App::create(defined('BASE_PATH') ? BASE_PATH : dir(__DIR__));

$app->bind('request', Request::class);
$app->bind('response', Response::class);

$app->addErrorMiddleware(true, true, true);

$app->addEventListeners([
    'server.onStart' => [
        OnStartListener::class,
    ],
    'server.onWorkerStart' => [
        OnWorkerStartListener::class,
    ],
]);

return $app;
