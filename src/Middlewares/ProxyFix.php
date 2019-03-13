<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-02-13 16:13:59 +0800
 */
namespace SlimExtra\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProxyFix
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if ($scheme = $request->getServerParam('HTTP_X_FORWARDED_PROTO')) {
            $uri = $request->getUri()->withScheme($scheme);
            $request = $request->withUri($uri);
        }

        return $next($request, $response);
    }
}
