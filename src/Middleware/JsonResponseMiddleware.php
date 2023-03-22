<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:11:49 +0800
 */

namespace Teddy\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Teddy\Http\Response;
use Throwable;

class JsonResponseMiddleware implements MiddlewareInterface
{
    protected array $logExceptions = [];

    protected array $exceptExceptions = [Exception::class];

    public function __construct(array $logExceptions = [], array $exceptExceptions = [])
    {
        $this->logExceptions    = array_merge($this->logExceptions, $logExceptions);
        $this->exceptExceptions = array_merge($this->exceptExceptions, $exceptExceptions);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $eClassName = get_class($e);
            if (!in_array($eClassName, $this->exceptExceptions, true) && (!$this->logExceptions || in_array($eClassName, $this->logExceptions, true))) {
                log_exception($e);
            }

            /** @var Response */
            $response = response();

            return $response->json($e);
        }
    }
}
