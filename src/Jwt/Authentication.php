<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-03 11:08:55 +0800
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
        try {
            $request = app('jwt')->processRequest($request);
        } catch (Exception $e) {
            if ($this->errorHandler) {
                $resp = call_user_func($this->errorHandler, $request, $response, $e);
                if ($resp instanceof ResponseInterface) {
                    return $resp;
                }
            }

            $request = $request->withAttribute('authError', $e);
        }

        return $next($request, $response);
    }
}
