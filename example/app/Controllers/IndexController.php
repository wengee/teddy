<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 18:50:39 +0800
 */

namespace App\Controllers;

use App\Models\Qrcode;
use App\Tasks\Demo;
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
        $e = (new Demo)->result();

        return $response->json(0, compact(['a', 'b', 'c', 'd', 'e']));
    }
}
