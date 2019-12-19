<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-19 16:47:10 +0800
 */

namespace Teddy;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Teddy\Factory\ServerRequestFactory;
use Teddy\Swoole\Server;

class App extends BaseApp
{
    public function run(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        $request = ServerRequestFactory::createServerRequestFromSwoole($swooleRequest);
        $response = $this->slimInstance->handle($request);
        $responseEmitter = new ResponseEmitter($swooleResponse);
        $responseEmitter->emit($response);
    }

    public function listen(): void
    {
        $config = $this->config->get('swoole', []);
        (new Server($this, $config))->start();
    }
}
