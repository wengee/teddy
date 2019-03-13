<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-06 13:43:01 +0800
 */
namespace SlimExtra\Jwt;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Authentication
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $jwtHelper = app('jwt');
        if ($jwtHelper) {
            $request = $jwtHelper->processRequest($request);
        }

        return $next($request, $response);
    }
}
