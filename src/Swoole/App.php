<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-06-10 11:14:57 +0800
 */

namespace Teddy\Swoole;

use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Teddy\Abstracts\AbstractApp;

class App extends AbstractApp
{
    public function run(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        $request = ServerRequestFactory::createServerRequestFromSwoole($swooleRequest);
        $response = $this->slimInstance->handle($request);
        $responseEmitter = new ResponseEmitter($swooleResponse);
        $responseEmitter->emit($response);
    }

    public function listen($host = null): void
    {
        $config = (array) $this->config->get('swoole', []);
        if (is_int($host) && $host > 0) {
            $config['port'] = $host;
        } elseif (is_string($host)) {
            $arr = explode(':', $host);
            $config['host'] = $arr[0] ?? '0.0.0.0';
            $config['port'] = intval($arr[1] ?? 9500);
        }

        (new Server($this, $config))->start();
    }
}
