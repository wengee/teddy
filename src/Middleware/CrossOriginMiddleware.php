<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:43:44 +0800
 */

namespace Teddy\Middleware;

use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CrossOriginMiddleware implements MiddlewareInterface
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $methodLine = '';

    /**
     * @var string
     */
    protected $headerLine = '';

    public function __construct()
    {
        $this->config     = (array) config('cors');
        $this->methodLine = implode(',', $this->config['methods']);
        $this->headerLine = implode(',', $this->config['headers']);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->config['intercept'] && 'OPTIONS' === $request->getMethod()) {
            $response = response();
        } else {
            $response = $handler->handle($request);
        }

        return $this->acceptCors($request, $response);
    }

    protected function acceptCors(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $origin = $request->getHeaderLine('ORIGIN');
        if ($this->checkOrigin($origin)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Methods', $this->methodLine)
                ->withHeader('Access-Control-Allow-Headers', $this->headerLine)
            ;

            if ($this->config['withCredentials']) {
                $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            }
        }

        return $response;
    }

    protected function checkOrigin(?string $origin): bool
    {
        if (empty($origin)) {
            return false;
        }

        if ('*' === $this->config['origin']) {
            return true;
        }

        return Str::is($this->config['origin'], $origin);
    }
}
