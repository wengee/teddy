<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 16:59:59 +0800
 */

namespace Teddy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProxyFixMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($scheme = $request->getServerParam('HTTP_X_FORWARDED_PROTO')) {
            $uri = $request->getUri()->withScheme($scheme);
            $request = $request->withUri($uri);
        }

        return $handler->handle($request);
    }
}
