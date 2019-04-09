<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-09 15:16:55 +0800
 */
namespace Teddy\Jwt;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Teddy\Traits\HasOptions;
use Teddy\Traits\HasUriMatch;

class Authentication
{
    use HasOptions, HasUriMatch;

    protected $options = [
        'path' => null,
        'ignore' => null,
    ];

    protected $callback;

    public function __construct(array $options = [])
    {
        $this->hydrate($options);
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        $exception = null;
        try {
            $request = app('jwt')->processRequest($request);
        } catch (Exception $e) {
            $exception = $e;
            $request = $request->withAttribute('jwtError', $e);
        }

        if (!$this->isUriMatch($request, $this->conditions)) {
            return $next($request, $response);
        } elseif ($this->callback) {
            $resp = call_user_func($this->callback, $request, $response, $exception);
            if ($resp instanceof ResponseInterface) {
                return $resp;
            }
        }

        return $next($request, $response);
    }

    protected function setCallback(callable $callback)
    {
        $this->callback = $callback;
    }
}
