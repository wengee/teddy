<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-14 17:07:18 +0800
 */
namespace Teddy\Jwt;

use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class JwtHelper
{
    private $options = [
        'secret' => 'This is a secret!',
        'secure' => true,
        'relaxed' => ['localhost', '127.0.0.1'],
        'algorithm' => ['HS256', 'HS512', 'HS384'],
        'header' => 'Authorization',
        'regexp' => "/Bearer\s+(.*)$/i",
        'cookie' => 'token',
        'param' => 'token',
        'attribute' => 'user',
        'userClass' => null,
    ];

    private $secretSuffix;

    public function __construct(array $options = [])
    {
        $this->hydrate($options);
    }

    public function processRequest(ServerRequestInterface $request): ServerRequestInterface
    {
        try {
            $token = $this->fetchToken($request);
        } catch (Exception $e) {
            throw $e;
        }

        try {
            $payload = $this->decodeToken($token);
        } catch (Exception $e) {
            throw $e;
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

        /* Check for token in header. */
        $headers = $request->getHeader($this->options['header']);
        $header = isset($headers[0]) ? $headers[0] : '';

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

    /**
     * Decode the token.
     */
    public function decodeToken(string $token): array
    {
        try {
            $decoded = JWT::decode(
                $token,
                $this->options['secret'] . $this->secretSuffix,
                (array) $this->options['algorithm']
            );
            return (array) $decoded;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Encode the payload.
     */
    public function encodeToken(array $payload, int $ttl = 0): string
    {
        $timestamp = time();
        $payload['iat'] = $timestamp;
        if ($ttl > 0) {
            $payload['exp'] = $timestamp + $ttl;
        }

        $algorithm = (array) $this->options['algorithm'];
        $alg = isset($algorithm[0]) ? $algorithm[0] : 'HS256';
        return JWT::encode(
            $payload,
            $this->options['secret'] . $this->secretSuffix,
            $alg
        );
    }

    public function setSecret(?string $secret)
    {
        $this->secretSuffix = $secret;
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
