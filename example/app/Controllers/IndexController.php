<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-20 10:35:52 +0800
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
        $query = Qrcode::query()
            ->select()
            ->where([
                ['id', 0],
                ['id', '>', 100],
                ['id', '<>', [1, 20]],
            ], 'OR')
            ->where([
                ['code', 'abc'],
                ['code', '~', 'c%'],
            ], 'OR')
            ->where([
                ['status', '!=', null],
                ['status', '>', 100],
                ['status', '!=', [1,2,3]],
                [['status', 'code'], '%', '*a*']
            ], 'OR');

        $b = [];
        $a = $query->getSql($b);

        app('swoole')->aTable->set(uniqid(), ['a' => 123, 'b' => 'abc']);
        $c = app('swoole')->aTable->count();
        $d = [];
        foreach (app('swoole')->aTable as $key => $value) {
            $d[$key] = $value;
        }

        return $response->json(0, compact(['a', 'b', 'c', 'd']));
    }
}
