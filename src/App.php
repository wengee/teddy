<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-20 16:59:56 +0800
 */

namespace Teddy;

use Slim\ResponseEmitter;
use Teddy\Abstracts\AbstractApp;
use Teddy\Factory\ServerRequestFactory;

class App extends AbstractApp
{
    public function run(): void
    {
        $request = ServerRequestFactory::createServerRequest();
        $response = $this->slimInstance->handle($request);
        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
    }
}
