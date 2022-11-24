<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-24 17:24:25 +0800
 */

namespace Teddy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teddy\Http\Request;

class AccessLogMiddleware implements MiddlewareInterface
{
    protected $logger = 'access';

    public function __construct(?string $logger = null)
    {
        if ($logger !== null) {
            $this->logger = $logger;
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $sTime    = microtime(true);
        $response = $handler->handle($request);
        $eTime    = microtime(true);

        /**
         * @var Request $request
         */
        log_message(
            $this->logger,
            'INFO',
            '%s - "%s %s" - %d - %.2fms',
            $request->getClientIp(),
            $request->getMethod(),
            $request->getUri()->getPath(),
            $response->getStatusCode(),
            ($eTime - $sTime) * 1000
        );

        return $response;
    }
}
