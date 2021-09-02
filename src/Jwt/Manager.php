<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-31 11:53:09 +0800
 */

namespace Teddy\Jwt;

use Exception;
use Firebase\JWT\JWT;
use Illuminate\Support\Arr;

class Manager
{
    /** @var array */
    protected $config;

    public function __construct()
    {
        $this->config = config('jwt');
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

        $cacheKey = 'jwt:block:'.$token;

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

        $cacheKey = 'jwt:block:'.$token;

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
        $secret    = $options['secret'] ?? $this->config['secret'];
        $algorithm = $options['algorithm'] ?? $this->config['algorithm'];
        $algorithm = Arr::wrap($algorithm);

        try {
            $decoded = JWT::decode($token, $secret, $algorithm);

            return (array) $decoded;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Encode the payload.
     */
    public function encode(array $payload, int $ttl = 0, array $options = []): string
    {
        $secret    = $options['secret'] ?? $this->config['secret'];
        $algorithm = $options['algorithm'] ?? $this->config['algorithm'];
        $algorithm = Arr::wrap($algorithm);

        $timestamp      = time();
        $payload['iat'] = $timestamp;
        if ($ttl > 0) {
            $payload['exp'] = $timestamp + $ttl;
        }

        $alg = $algorithm[0] ?? 'HS256';

        return JWT::encode($payload, $secret, $alg);
    }
}
