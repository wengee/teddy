<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-12 18:00:42 +0800
 */

namespace App\Controllers;

use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;
use Teddy\Swoole\Coroutine;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $a = (string) $request->getUri();
        $c = db()->table('user')->select()->limit(1)->all();
        $d = app('redis')->keys('*');
        $b = app('jwt')->encode(['abc']);
        $e = app('server')->stats();

        return $response->json(0, compact(['a', 'b', 'c', 'd', 'e']));
    }
}
