<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-27 15:20:09 +0800
 */
namespace Teddy\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Cors
{
    protected $options = [
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
    ];

    protected $methodLine;

    protected $headerLine;

    public function __construct(array $options = [])
    {
        $this->hydrate($options);
        $this->methodLine = implode(',', $this->options['methods']);
        $this->headerLine = implode(',', $this->options['headers']);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if ($this->options['intercept'] && $request->getMethod() === 'OPTIONS') {
            return $this->acceptCors($request, $response);
        }

        $response = $next($request, $response);
        return $this->acceptCors($request, $response);
    }

    protected function hydrate($data = []): void
    {
        foreach ($data as $key => $value) {
            $key = str_replace('.', ' ', $key);
            $method = lcfirst(ucwords($key));
            $method = str_replace(' ', '', $method);
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $value);
            } else {
                $this->options[$key] = $value;
            }
        }
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

        $allowedOrigin = (array) $this->options['origin'];
        foreach ($allowedOrigin as $value) {
            if (str_is($value, $origin)) {
                return true;
            }
        }

        return false;
    }

    protected function methods($methods)
    {
        $methods = (array) $methods;
        if (array_get($methods, 'replace')) {
            $this->options['methods'] = array_get($methods, 'value');
        } else {
            $this->options['methods'] = array_merge($this->options['methods'], $methods);
        }
    }

    protected function headers($headers)
    {
        $headers = (array) $headers;
        if (array_get($headers, 'replace')) {
            $this->options['headers'] = array_get($headers, 'value');
        } else {
            $this->options['headers'] = array_merge($this->options['headers'], $headers);
        }
    }
}
