<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-07 18:34:47 +0800
 */

namespace Teddy\Providers;

use Exception;
use Firebase\JWT\JWT as JwtEncoder;
use Psr\Http\Message\ServerRequestInterface;
use Teddy\Interfaces\JwtUserInterface;
use Teddy\Options;

class Jwt
{
    protected $options;

    protected $secretSuffix;

    public function __construct()
    {
        $this->options = (new Options([
            'secret'    => 'This is a secret!',
            'secure'    => true,
            'relaxed'   => ['localhost', '127.0.0.1'],
            'algorithm' => ['HS256', 'HS512', 'HS384'],
            'header'    => 'Authorization',
            'regexp'    => '/Bearer\\s+(.*)$/i',
            'cookie'    => 'token',
            'param'     => 'token',
            'attribute' => 'user',
        ], true));

        $config = config('jwt');
        if ($config && is_array($config)) {
            $this->options->update($config);
        }
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
            $decoded = JwtEncoder::decode(
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
        return JwtEncoder::encode(
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
}
