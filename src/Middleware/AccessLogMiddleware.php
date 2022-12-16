<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-24 21:48:15 +0800
 */

namespace Teddy\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teddy\Http\Request;

class AccessLogMiddleware implements MiddlewareInterface
{
    /** @var string */
    protected $logger = 'access';

    /** @var string */
    protected $format = '{client_ip} - "{method} {path}" - {status_code} - {elapsed_time}';

    /**
     * @param null|string $logger Default: access
     * @param null|string $format Default: {client_ip} - "{method} {path}" - {status_code} - {elapsed_time}
     */
    public function __construct(?string $logger = null, ?string $format = null)
    {
        if (null !== $logger) {
            $this->logger = $logger;
        }

        if (null !== $format) {
            $this->format = $format;
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
        log_message($this->logger, 'INFO', strtr($this->format, [
            '{client_ip}'    => $request->getClientIp(),
            '{method}'       => $request->getMethod(),
            '{path}'         => $request->getUri()->getPath(),
            '{query_string}' => $request->getUri()->getQuery(),
            '{status_code}'  => $response->getStatusCode(),
            '{elapsed_time}' => sprintf('%.2fms', ($eTime - $sTime) * 1000),
        ]));

        return $response;
    }
}
