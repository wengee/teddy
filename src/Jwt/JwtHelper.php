<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-09 15:08:55 +0800
 */
namespace Teddy\Jwt;

use Exception;
use Firebase\JWT\JWT;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Teddy\Traits\HasOptions;

class JwtHelper
{
    use HasOptions;

    protected $options = [
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

    protected $secretSuffix;

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

        $request = $request->withAttribute('jwtPayload', $payload);
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

    public function setSecretSuffix(?string $secretSuffix)
    {
        $this->secretSuffix = $secretSuffix;
        return $this;
    }

    protected function setSecret(string $secret): void
    {
        $this->options['secret'] = $secret;
    }

    protected function setAttribute(string $attribute): void
    {
        $this->options['attribute'] = $attribute;
    }

    protected function setHeader(string $header): void
    {
        $this->options['header'] = $header;
    }

    protected function setRegexp(string $regexp): void
    {
        $this->options['regexp'] = $regexp;
    }

    protected function setAlgorithm($algorithm): void
    {
        $this->options['algorithm'] = (array) $algorithm;
    }
}
