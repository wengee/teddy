<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 15:08:20 +0800
 */

namespace App\Controllers;

use App\Models\Qrcode;
use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $a = Qrcode::query()->first();
        $a['status'] += 11;
        $b = $a->save();
        $c = [md5('哈哈'), '中华人民共和国'];
        $d = app('redis')->keys('*');
        $e = app('server')->stats();

        return $response->json(0, compact(['a', 'b', 'c', 'd', 'e']));
    }
}
