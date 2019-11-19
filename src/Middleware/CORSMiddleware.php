<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-19 14:36:25 +0800
 */

namespace Teddy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teddy\Options;
use Teddy\Traits\HasOptions;
use Teddy\Traits\HasUriMatch;

class CORSMiddleware implements MiddlewareInterface
{
    use HasOptions, HasUriMatch;

    protected $conditions = [
        'path' => null,
        'ignore' => null,
    ];

    protected $options;

    protected $methodLine;

    protected $headerLine;

    public function __construct(array $options = [])
    {
        $this->options = new Options([
            'intercept' => true,
            'origin' => '*',
            'methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'headers' => [
                'Accept',
                'Accept-Language',
                'User-Agent',
                'X-Requested-With',
                'If-Modified-Since',
                'Cache-Control',
                'Content-Type',
                'Range',
                'Authorization',
            ],
        ]);

        $this->setOptions($options);
        $this->init();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isUriMatch($request, $this->conditions)) {
            return $handler->handle($request);
        }

        if ($this->options['intercept'] && $request->getMethod() === 'OPTIONS') {
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
                                 ->withHeader('Access-Control-Allow-Headers', $this->headerLine);
        }

        return $response;
    }

    protected function checkOrigin(?string $origin): bool
    {
        if ($this->options['origin'] === '*') {
            return true;
        } elseif (empty($origin)) {
            return false;
        }

        return str_is($this->options['origin'], $origin);
    }

    protected function setMethods($methods): void
    {
        $methods = (array) $methods;
        if (array_get($methods, 'replace')) {
            $this->options['methods'] = array_get($methods, 'value');
        } else {
            $this->options['methods'] = array_merge($this->options['methods'], $methods);
        }
    }

    protected function setHeaders($headers): void
    {
        $headers = (array) $headers;
        if (array_get($headers, 'replace')) {
            $this->options['headers'] = array_get($headers, 'value');
        } else {
            $this->options['headers'] = array_merge($this->options['headers'], $headers);
        }
    }

    protected function setPath($path): void
    {
        $this->conditions['path'] = (array) $path;
    }

    protected function setIgnore($ignore): void
    {
        $this->conditions['ignore'] = (array) $ignore;
    }

    protected function init(): void
    {
        $this->methodLine = implode(',', $this->options['methods']);
        $this->headerLine = implode(',', $this->options['headers']);
    }
}
