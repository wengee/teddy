<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-09 14:34:06 +0800
 */
namespace Teddy;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Route as SlimRoute;

class Route extends SlimRoute
{
    public function run(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $response = parent::run($request, $response);
        } catch (Exception $e) {
            $response = app('errorHandler')($request, $response, $e);
        }

        return $response;
    }
}
