<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 11:24:41 +0800
 */

namespace Teddy\Middleware;

use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teddy\Traits\HasUriMatch;

class CORSMiddleware implements MiddlewareInterface
{
    use HasUriMatch;

    protected $options;

    protected $methodLine;

    protected $headerLine;

    public function __construct(array $options = [])
    {
        $this->options    = new CORSMiddlewareOptions($options);
        $this->methodLine = implode(',', $this->options['methods']);
        $this->headerLine = implode(',', $this->options['headers']);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isUriMatch($request, $this->options['conditions'])) {
            return $handler->handle($request);
        }

        if ($this->options['intercept'] && 'OPTIONS' === $request->getMethod()) {
            $response = make('response', []);
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

            if ($this->options['withCredentials']) {
                $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            }
        }

        return $response;
    }

    protected function checkOrigin(?string $origin): bool
    {
        if ('*' === $this->options['origin']) {
            return true;
        }
        if (empty($origin)) {
            return false;
        }

        return Str::is($this->options['origin'], $origin);
    }
}
