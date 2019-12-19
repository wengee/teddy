<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-19 17:46:27 +0800
 */

namespace Teddy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Stream;

class StaticFileMiddleware implements MiddlewareInterface
{
    protected $basePath;

    protected $urlPrefix;

    public function __construct(string $basePath, string $urlPrefix = '')
    {
        $this->basePath = rtrim($basePath, '/') . '/';
        $this->urlPrefix = $urlPrefix ? '/' . ltrim($urlPrefix, '/') : '';
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        if (!$this->urlPrefix || strpos($path, $this->urlPrefix) === 0) {
            $filePath = $this->basePath . ltrim($path, '/');
            if (is_file($filePath)) {
                return $this->sendFile($filePath);
            } elseif (is_dir($filePath)) {
                $filePath = rtrim($filePath, '/') . '/index.html';
                if (is_file($filePath)) {
                    return $this->sendFile($filePath);
                }
            }
        }

        return $handler->handle($request);
    }

    protected function sendFile(string $filePath): ResponseInterface
    {
        return make('response', [200])
            ->withHeader('Content-Type', \mime_content_type($filePath))
            ->withBody(new Stream(fopen($filePath, 'r')));
    }
}
