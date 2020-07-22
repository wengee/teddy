<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-07-20 14:33:58 +0800
 */

namespace Teddy\Auth;

use Exception;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Teddy\Traits\HasOptions;
use Teddy\Traits\HasUriMatch;

class Authentication implements MiddlewareInterface
{
    use HasOptions, HasUriMatch;

    protected $conditions = [
        'path' => null,
        'ignore' => null,
    ];

    protected $options = [
        'header'    => 'Authorization',
        'regexp'    => '/^Bearer\\s+(.*)$/i',
        'cookie'    => 'token',
        'param'     => 'token',
        'attribute' => 'user',
    ];

    protected $callback;

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->isUriMatch($request, $this->conditions)) {
            return $handler->handle($request);
        }

        $token = $payload = $user = null;
        try {
            $token = $this->fetchToken($request);
        } catch (Exception $e) {
        }

        if ($token) {
            $payload = app('auth')->fetch($token);
            if ($payload && is_callable($this->callback)) {
                $user = call_user_func($this->callback, $request, $payload);
            }
        }

        $request = $request->withAttribute('authToken', $token)
                           ->withAttribute('authData', $payload)
                           ->withAttribute($this->options['attribute'], $user);

        return $handler->handle($request);
    }

    /**
     * Fetch the access token.
     */
    protected function fetchToken(ServerRequestInterface $request): string
    {
        $header = '';

        /* Check for token in header. */
        $headers = $request->getHeader($this->options['header']);
        $header = trim($headers[0] ?? '');

        if (preg_match($this->options['regexp'], $header, $matches)) {
            return $matches[1];
        }

        $params = $request->getParams();
        if (isset($params[$this->options['param']])) {
            return $params[$this->options['param']];
        }

        /* Token not found in header try a cookie. */
        $cookieParams = $request->getCookieParams();
        if (isset($cookieParams[$this->options['cookie']])) {
            return $cookieParams[$this->options['cookie']];
        };

        /* If everything fails log and throw. */
        throw new RuntimeException('Token not found.');
    }

    protected function setPath($path): void
    {
        $this->conditions['path'] = Arr::wrap($path);
    }

    protected function setIgnore($ignore): void
    {
        $this->conditions['ignore'] = Arr::wrap($ignore);
    }

    protected function setCallback($callback): void
    {
        if (\is_callable($callback)) {
            $this->callback = $callback;
        }
    }
}
