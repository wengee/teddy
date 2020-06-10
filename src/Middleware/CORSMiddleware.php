<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-06-10 12:14:40 +0800
 */

namespace Teddy\Middleware;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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
            'withCredentials' => false,
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

            if ($this->options['withCredentials']) {
                $response = $response->withHeader('Access-Control-Allow-Credentials', 'true');
            }
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

        return Str::is($this->options['origin'], $origin);
    }

    protected function setMethods($methods): void
    {
        $methods = Arr::wrap($methods);
        if (Arr::get($methods, 'replace')) {
            $this->options['methods'] = Arr::get($methods, 'value');
        } else {
            $this->options['methods'] = array_merge($this->options['methods'], $methods);
        }
    }

    protected function setHeaders($headers): void
    {
        $headers = Arr::wrap($headers);
        if (Arr::get($headers, 'replace')) {
            $this->options['headers'] = Arr::get($headers, 'value');
        } else {
            $this->options['headers'] = array_merge($this->options['headers'], $headers);
        }
    }

    protected function setPath($path): void
    {
        $this->conditions['path'] = Arr::wrap($path);
    }

    protected function setIgnore($ignore): void
    {
        $this->conditions['ignore'] = Arr::wrap($ignore);
    }

    protected function init(): void
    {
        $this->methodLine = implode(',', $this->options['methods']);
        $this->headerLine = implode(',', $this->options['headers']);
    }
}
