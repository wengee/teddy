<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-13 15:59:22 +0800
 */
namespace Teddy\Jwt;

use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class JwtHelper
{
    private $options = [
        'secure' => true,
        'relaxed' => ['localhost', '127.0.0.1'],
        'algorithm' => ['HS256', 'HS512', 'HS384'],
        'header' => 'Authorization',
        'regexp' => "/Bearer\s+(.*)$/i",
        'cookie' => 'token',
        'attribute' => 'user',
        'userClass' => null,
    ];

    public function __construct(array $options = [])
    {
        $this->hydrate($options);
    }

    public function processRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        try {
            $token = $this->fetchToken($request);
        } catch (Exception $e) {
            return $request;
        }

        try {
            $payload = $this->decodeToken($token);
        } catch (Exception $e) {
            return $request;
        }

        $userClass = $this->options['userClass'];
        if ($userClass && \is_subclass_of($userClass, JwtUserInterface::class)) {
            $user = $userClass::retrieveByPayload($payload);
        } else {
            $user = $payload;
        }

        return $request->withAttribute($this->options['attribute'], $user);
    }

    /**
     * Fetch the access token.
     */
    public function fetchToken(ServerRequestInterface $request): string
    {
        $header = '';
        $message = 'Using token from request header';

        /* Check for token in header. */
        $headers = $request->getHeader($this->options['header']);
        $header = isset($headers[0]) ? $headers[0] : '';

        if (preg_match($this->options['regexp'], $header, $matches)) {
            return $matches[1];
        }

        /* Token not found in header try a cookie. */
        $cookieParams = $request->getCookieParams();
        if (isset($cookieParams[$this->options['cookie']])) {
            return $cookieParams[$this->options['cookie']];
        };

        /* If everything fails log and throw. */
        throw new RuntimeException('Token not found.');
    }

    /**
     * Decode the token.
     */
    public function decodeToken(string $token): array
    {
        try {
            $decoded = JWT::decode(
                $token,
                $this->options['secret'],
                (array) $this->options['algorithm']
            );
            return (array) $decoded;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Encode the payload.
     */
    public function encodeToken(array $payload, int $ttl = 0):string
    {
        $timestamp = time();
        $payload['iat'] = $timestamp;
        if ($ttl > 0) {
            $payload['exp'] = $timestamp + $ttl;
        }

        return JWT::encode($payload, $this->options['secret']);
    }

    public function setSecret(string $secret)
    {
        $this->options['secret'] = $secret;
        return $this;
    }

    private function hydrate($data = []): void
    {
        foreach ($data as $key => $value) {
            /* https://github.com/facebook/hhvm/issues/6368 */
            $key = str_replace('.', ' ', $key);
            $method = lcfirst(ucwords($key));
            $method = str_replace(' ', '', $method);
            if (method_exists($this, $method)) {
                /* Try to use setter */
                call_user_func([$this, $method], $value);
            } else {
                /* Or fallback to setting option directly */
                $this->options[$key] = $value;
            }
        }
    }

    /**
     * Set the attribute name used to attach decoded token to request.
     */
    private function attribute(string $attribute): void
    {
        $this->options['attribute'] = $attribute;
    }

    /**
     * Set the header where token is searched from.
     */
    private function header(string $header): void
    {
        $this->options['header'] = $header;
    }

    /**
     * Set the regexp used to extract token from header or environment.
     */
    private function regexp(string $regexp): void
    {
        $this->options['regexp'] = $regexp;
    }

    /**
     * Set the allowed algorithms
     */
    private function algorithm($algorithm): void
    {
        $this->options['algorithm'] = (array) $algorithm;
    }
}
