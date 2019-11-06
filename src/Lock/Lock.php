<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-06 14:12:30 +0800
 */

namespace Teddy\Lock;

use Teddy\Exception;
use Teddy\Interfaces\LockInterface;

class Lock implements LockInterface
{
    protected $key;

    protected $ttl = -1;

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
            if redis.call("EXISTS", KEYS[1]) then
                return 0
            elseif redis.call("SET", KEYS[1], ARGV[1], "EX", ARGV[2]) then
                return 1
            else
                return 0
            end
        ';

        return !!$this->evaluate(
            $script,
            (string) $this->key,
            [$this->key->getUniqueToken(), $this->ttl]
        );
    }

    public function refresh(?int $ttl = null): bool
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("EXPIRE", KEYS[1], ARGV[2]
            else
                return 0
            end
        ';

        $ttl = $ttl === null ? $this->ttl : $ttl;
        return !!$this->evaluate(
            $script,
            (string) $this->key,
            [$this->key->getUniqueToken(), $ttl]
        );
    }

    public function isAcquired(): bool
    {
        $script = 'return redis.call("GET", KEYS[1]) == ARGV[1]';

        return !!$this->evaluate(
            $script,
            (string) $this->key,
            [$this->key->getUniqueToken()]
        );
    }

    public function release(): bool
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';

        return !!$this->evaluate(
            $script,
            (string) $this->key,
            [$this->key->getUniqueToken()]
        );
    }

    protected function evaluate(string $script, string $resource, array $args)
    {
        return $this->redis->eval($script, array_merge([$resource], $args), 1);
    }
}
