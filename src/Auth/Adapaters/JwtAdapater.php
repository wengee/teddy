<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:30:54 +0800
 */

namespace Teddy\Auth\Adapaters;

use Exception;
use Firebase\JWT\JWT;
use Teddy\Config\Repository;
use Teddy\Interfaces\AuthAdapaterInterface;

class JwtAdapater implements AuthAdapaterInterface
{
    /**
     * @var array
     */
    protected $options = [];

    public function __construct(array $options)
    {
        $this->options = (new Repository([
            'secret'    => 'This is a secret!',
            'algorithm' => new Repository(['HS256', 'HS512', 'HS384'], Repository::DATA_AS_LIST | Repository::DATA_PROTECTED),
            'block'     => new Repository([
                'enabled' => true,
                'prefix'  => 'jwt:block:',
                'expires' => 86400 * 7,
            ]),
        ]))->merge($options)->toArray();
    }

    public function encode(array $data, int $expiresIn = 0): string
    {
        $secret    = $this->options['secret'];
        $algorithm = $this->options['algorithm'];
        $timestamp = time();
        $payload   = ['data' => $data, 'iat' => $timestamp];
        if ($expiresIn > 0) {
            $payload['exp'] = $timestamp + $expiresIn;
        }

        return JWT::encode($payload, $secret, $algorithm[0] ?? 'HS256');
    }

    public function decode(string $token): ?array
    {
        if ($this->isBlocked($token)) {
            return null;
        }

        $secret    = $this->options['secret'];
        $algorithm = $this->options['algorithm'];
        $payload   = JWT::decode($token, $secret, $algorithm);

        return $payload ? ($payload['data'] ?? null) : null;
    }

    public function block(string $token): void
    {
        if (empty($this->options['block']['enabled'])) {
            return;
        }

        $redis = app('redis');
        if (!$redis) {
            return;
        }

        $cacheKey = $this->options['block']['prefix'].$token;
        $redis->set($cacheKey, time(), (int) $this->options['block']['expires']);
    }

    protected function isBlocked(string $token): bool
    {
        if (empty($this->options['block']['enabled'])) {
            return false;
        }

        $redis = app('redis');
        if (!$redis) {
            return false;
        }

        $cacheKey = $this->options['block']['prefix'].$token;

        try {
            return (bool) $redis->exists($cacheKey);
        } catch (Exception $e) {
            log_exception($e);

            return false;
        }
    }
}
