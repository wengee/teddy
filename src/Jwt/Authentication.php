<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 17:30:22 +0800
 */

namespace Teddy\Jwt;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Authentication implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwt = app('jwt');
        if ($jwt) {
            try {
                $request = $jwt->processRequest($request);
            } catch (Exception $e) {
                log_exception($e);
            }
        }

        return $handler->handle($request);
    }
}
