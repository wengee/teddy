<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-13 18:03:58 +0800
 */

use App\Events\Example;
use App\Http\Request;
use App\Http\Response;
use App\Listeners\ExampleListener;
use Teddy\App;

$app = App::create(defined('BASE_PATH') ? BASE_PATH : dir(__DIR__));

$app->bind('request', Request::class);
$app->bind('response', Response::class);

$app->addErrorMiddleware(false, false, false);

$app->addEventListeners([
    Example::class => [
        ExampleListener::class,
    ],
]);

return $app;
