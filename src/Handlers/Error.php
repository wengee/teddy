<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-09 14:21:05 +0800
 */
namespace Teddy\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\Error as SlimErrorHandler;
use Teddy\JsonInterface;

class Error extends SlimErrorHandler
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception)
    {
        if ($exception instanceof JsonInterface) {
            return $response->withJson($exception, 200);
        }

        return parent::__invoke($request, $response, $exception);
    }
}
