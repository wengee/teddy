<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-13 17:53:45 +0800
 */

namespace App\Controllers;

use App\Events\Example;
use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $a = "\x01";
        $b = md5($a);
        $c = [md5('哈哈'), md5($a . '哈哈'), '中华人民共和国'];
        $d = app('redis')->keys('*');
        $e = app('server')->stats();

        event(new Example);
        return $response->json(0, compact(['a', 'b', 'c', 'd', 'e']));
    }
}
