<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teddy\Http\Request;

class ProxyFixMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (($request instanceof Request) && ($scheme = $request->getServerParam('HTTP_X_FORWARDED_PROTO'))) {
            $uri     = $request->getUri()->withScheme($scheme);
            $request = $request->withUri($uri);
        }

        return $handler->handle($request);
    }
}
