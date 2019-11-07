<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-07 18:12:12 +0800
 */

/**
 * @source https://github.com/hyperf/guzzle
 */

namespace Teddy\Guzzle;

use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Swoole\Coroutine\Http\Client;
use Teddy\Pool\SimplePool\PoolFactory;

class PoolHandler extends CoroutineHandler
{
    /**
     * @var array
     */
    protected $options;

    public function __construct(array $options = [])
    {
        $this->options = [
            'minConnections' => 1,
            'maxConnections' => 5,
            'maxIdleTime' => 60,
        ] + $options;
    }

    public function __invoke(RequestInterface $request, array $options)
    {
        $uri = $request->getUri();
        $host = $uri->getHost();
        $port = $uri->getPort();
        $ssl = $uri->getScheme() === 'https';
        $path = $uri->getPath();
        $query = $uri->getQuery();

        if (empty($port)) {
            $port = $ssl ? 443 : 80;
        }

        if (empty($path)) {
            $path = '/';
        }

        if ($query !== '') {
            $path .= '?' . $query;
        }

        $pool = PoolFactory::instance()->get(
            $this->getPoolName($uri),
            function () use ($host, $port, $ssl) {
                return new Client($host, $port, $ssl);
            },
            $this->options
        );

        $connection = $pool->get();
        try {
            $client = $connection->connect();
            $client->setMethod($request->getMethod());
            $client->setData((string) $request->getBody());
            $this->initHeaders($client, $request, $options);
            $settings = $this->getSettings($request, $options);

            if (!empty($settings)) {
                $client->set($settings);
            }

            $this->execute($client, $path);
            $ex = $this->checkStatusCode($client, $request);
            if ($ex !== true) {
                $connection->close();
                return \GuzzleHttp\Promise\rejection_for($ex);
            }

            $response = $this->getResponse($client);
        } finally {
            $pool->release($connection);
        }

        return new FulfilledPromise($response);
    }

    protected function getPoolName(UriInterface $uri)
    {
        return sprintf('guzzle.handler.%s.%d.%s', $uri->getHost(), $uri->getPort(), $uri->getScheme());
    }
}
