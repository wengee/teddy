<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-26 11:32:22 +0800
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
        $b = \serialize($a);
        $c = \unserialize($b);

        $c['status'] += 11;
        $d = $c->save();
        $e = vendor_path('autoload.php');

        return $response->json(0, compact(['a', 'b', 'c', 'd', 'e']));
    }
}
