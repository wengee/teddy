<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 11:23:55 +0800
 */

namespace Teddy\Auth;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Teddy\Traits\HasUriMatch;

class Authentication implements MiddlewareInterface
{
    use HasUriMatch;

    protected $options;

    public function __construct(array $options = [])
    {
        $this->options = new AuthenticationOptions($options);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isUriMatch($request, $this->options['conditions'])) {
            return $handler->handle($request);
        }

        $token = $payload = $user = null;

        try {
            $token = $this->fetchToken($request);
        } catch (Exception $e) {
        }

        if ($token) {
            $payload = app('auth')->fetch($token);
            if ($payload && is_callable($this->options['callback'])) {
                $user = call_user_func($this->options['callback'], $request, $payload);
            }
        }

        $request = $request->withAttribute('authToken', $token)
            ->withAttribute('authPayload', $payload)
            ->withAttribute($this->options['attribute'], $user)
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
        $headers = $request->getHeader($this->options['header']);
        $header  = trim($headers[0] ?? '');

        if (preg_match($this->options['regexp'], $header, $matches)) {
            return $matches[1];
        }

        /** @var \Teddy\Http\Request $request */
        $params = $request->getParams();
        if (isset($params[$this->options['param']])) {
            return $params[$this->options['param']];
        }

        // Token not found in header try a cookie.
        $cookieParams = $request->getCookieParams();
        if (isset($cookieParams[$this->options['cookie']])) {
            return $cookieParams[$this->options['cookie']];
        }

        // If everything fails log and throw.
        throw new RuntimeException('Token not found.');
    }
}
