<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-14 16:31:10 +0800
 */
namespace Teddy\Jwt;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Authentication
{
    protected $errorHandler;

    public function __construct(?callable $errorHandler = null)
    {
        $this->errorHandler = $errorHandler;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $jwtHelper = app('jwt');
        if ($jwtHelper) {
            try {
                $request = $jwtHelper->processRequest($request);
            } catch (Exception $e) {
                if ($this->errorHandler) {
                    return call_user_func($this->errorHandler, $request, $response, $e);
                }
            }
        }

        return $next($request, $response);
    }
}
