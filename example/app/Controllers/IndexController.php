<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-18 10:04:34 +0800
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
            ->where('code', 'abc')
            ->where([
                ['id', 0],
                ['id', '>', 100],
            ], 'OR');
        $a = (string) $query;
        return $response->json(0, compact(['a']));
    }
}
