<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 18:38:38 +0800
 */

namespace App\Controllers;

use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $a = $request->getAttribute('routingResults');
        $c = db()->table('user')->select()->limit(10)->all();

        app('logger')->info('test');
        app('redis')->set('a', time());
        $d = app('redis')->keys('*');
        $b = app('jwt')->encode(['abc']);
        $e = app('jwt')->decode($b);

        return $response->json(0, compact(['a', 'b', 'c', 'd', 'e']));
    }
}
