<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-26 17:07:39 +0800
 */

namespace Teddy\Auth;

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
            log_exception($e);
        }

        if ($token) {
            $payload = app('auth')->load($token);
            if ($payload && is_callable($this->config['callback'])) {
                $user = call_user_func($this->config['callback'], $request, $payload);
            }
        }

        $request = $request->withAttribute('authToken', $token)
            ->withAttribute('authPayload', $payload)
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
        $header  = trim($headers[0] ?? '');

        if (preg_match($this->config['regexp'], $header, $matches)) {
            return $matches[1];
        }

        /** @var \Teddy\Http\Request $request */
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
