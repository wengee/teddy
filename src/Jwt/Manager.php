<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-21 14:51:24 +0800
 */

namespace Teddy\Jwt;

use Exception;
use Firebase\JWT\JWT;
use Teddy\Traits\HasOptions;

class Manager
{
    use HasOptions;

    protected $options = [
        'secret'        => 'This is a secret!',
        'algorithm'     => ['HS256', 'HS512', 'HS384'],
    ];

    public function __construct()
    {
        $config = config('jwt');
        if ($config && is_array($config)) {
            $this->setOptions($config);
        }
    }

    /**
     * Block the token.
     */
    public function block(string $token, int $ttl = 0): bool
    {
        $redis = app('redis');
        if (!$redis) {
            return false;
        }

        $cacheKey = 'jwt:block:' . $token;
        try {
            $redis->set($cacheKey, time(), $ttl);
        } catch (Exception $e) {
            log_exception($e);
            return false;
        }

        return true;
    }

    /**
     * Check the token.
     */
    public function isBlocked(string $token): bool
    {
        $redis = app('redis');
        if (!$redis) {
            return false;
        }

        $cacheKey = 'jwt:block:' . $token;
        try {
            return (bool) $redis->exists($cacheKey);
        } catch (Exception $e) {
            log_exception($e);
            return false;
        }
    }

    /**
     * Decode the token.
     */
    public function decode(string $token, array $options = []): array
    {
        $secret = $options['secret'] ?? $this->options['secret'];
        $algorithm = $options['algorithm'] ?? $this->options['algorithm'];
        $algorithm = array_wrap($algorithm);

        try {
            $decoded = JWT::decode($token, $secret, $algorithm);
            return array_wrap($decoded);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Encode the payload.
     */
    public function encode(array $payload, int $ttl = 0, array $options = []): string
    {
        $secret = $options['secret'] ?? $this->options['secret'];
        $algorithm = $options['algorithm'] ?? $this->options['algorithm'];
        $algorithm = array_wrap($algorithm);

        $timestamp = time();
        $payload['iat'] = $timestamp;
        if ($ttl > 0) {
            $payload['exp'] = $timestamp + $ttl;
        }

        $alg = $algorithm[0] ?? 'HS256';
        return JWT::encode($payload, $secret, $alg);
    }
}
