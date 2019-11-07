<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-07 18:20:55 +0800
 */

namespace App\Controllers;

use GuzzleHttp\Client;
use Teddy\Controller;
use Teddy\Http\Request;
use Teddy\Http\Response;

class IndexController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $client = new Client;
        $res = $client->request('GET', 'http://www.baidu.com/');
        $a = (string) $res->getBody();
        return $response->json(0, compact(['a']));
    }
}
