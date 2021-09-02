<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-31 11:53:21 +0800
 */

namespace Teddy\Jwt;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Teddy\Config\Repository;

class Authentication implements MiddlewareInterface
{
    /** @var array */
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = (new Repository([
            'header'    => 'Authorization',
            'regexp'    => '/^Bearer\\s+(.*)$/i',
            'cookie'    => 'token',
            'param'     => 'token',
            'attribute' => 'user',
            'callback'  => null,
        ]))->merge($config)->toArray();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $payload = $user = null;

        try {
            $token = $this->fetchToken($request);
        } catch (Exception $e) {
        }

        $jwt = app('jwt');
        if ($token && !$jwt->isBlocked($token)) {
            try {
                $payload = $jwt->decode($token);
            } catch (Exception $e) {
            }
        }

        if ($payload && is_callable($this->config['callback'])) {
            $user = call_user_func($this->config['callback'], $request, $payload);
        }

        $request = $request->withAttribute('jwtToken', $token)
            ->withAttribute('jwtPayload', $payload)
            ->withAttribute($this->config['attribute'], $user)
        ;

        return $handler->handle($request);
    }

    /**
     * Fetch the access token.
     */
    protected function fetchToken(ServerRequestInterface $request): string
    {
        $header = '';

        // Check for token in header.
        $headers = $request->getHeader($this->config['header']);
        $header  = $headers[0] ?? '';

        if (preg_match($this->config['regexp'], $header, $matches)) {
            return $matches[1];
        }

        /** @var Teddy\Http\Request $request */
        $params = $request->getParams();
        if (isset($params[$this->config['param']])) {
            return $params[$this->config['param']];
        }

        // Token not found in header try a cookie.
        $cookieParams = $request->getCookieParams();
        if (isset($cookieParams[$this->config['cookie']])) {
            return $cookieParams[$this->config['cookie']];
        }

        // If everything fails log and throw.
        throw new RuntimeException('Token not found.');
    }
}
