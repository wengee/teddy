<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-23 11:16:23 +0800
 */

namespace Teddy\Lock;

use Teddy\Exception;
use Teddy\Interfaces\LockInterface;
use Teddy\Redis\Redis;

class Lock implements LockInterface
{
    protected Key $key;

    protected int $ttl = -1;

    /**
     * @var Redis
     */
    protected $redis;

    public function __construct(Key $key, int $ttl = -1)
    {
        $this->key = $key;
        $this->ttl = $ttl;

        $redis = app('redis');
        if (!$redis) {
            throw new Exception('Redis is required.');
        }

        $this->redis = $redis;
    }

    public function acquire(): bool
    {
        $script = '
            if redis.call("SETNX", KEYS[1], ARGV[1]) > 0 then
                redis.call("EXPIRE", KEYS[1], ARGV[2])
                return true
            else
                return false
            end
        ';

        return (bool) $this->evaluate(
            $script,
            $this->key->getName(),
            [$this->key->getUniqueToken(), $this->ttl]
        );
    }

    public function refresh(?int $ttl = null): bool
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("EXPIRE", KEYS[1], ARGV[2]) > 0
            else
                return false
            end
        ';

        $ttl = null === $ttl ? $this->ttl : $ttl;

        return (bool) $this->evaluate(
            $script,
            $this->key->getName(),
            [$this->key->getUniqueToken(), $ttl]
        );
    }

    public function isAcquired(): bool
    {
        $script = 'return redis.call("GET", KEYS[1]) == ARGV[1]';

        return (bool) $this->evaluate(
            $script,
            $this->key->getName(),
            [$this->key->getUniqueToken()]
        );
    }

    public function release(): bool
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1]) > 0
            else
                return false
            end
        ';

        return (bool) $this->evaluate(
            $script,
            $this->key->getName(),
            [$this->key->getUniqueToken()]
        );
    }

    protected function evaluate(string $script, string $resource, array $args)
    {
        return $this->redis->eval($script, [$resource, ...$args], 1);
    }
}
