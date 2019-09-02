<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-09-02 16:45:39 +0800
 */

namespace App\Controllers;

use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $a = $request->getAttributes();
        return $response->json(0, compact(['a']));
    }
}
