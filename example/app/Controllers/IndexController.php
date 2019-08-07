<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 10:30:13 +0800
 */

namespace App\Controllers;

use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $a = get_class($request);
        $b = get_class($response);
        $c = spl_object_hash(app());
        return $response->json(0, compact(['a', 'b', 'c']));
    }
}
