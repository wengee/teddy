<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 16:32:01 +0800
 */

use App\Http\Request;
use App\Http\Response;
use Teddy\App;

$app = App::create(defined('BASE_PATH') ? BASE_PATH : dir(__DIR__));

$app->bind('request', Request::class);
$app->bind('response', Response::class);

return $app;
