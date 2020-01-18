<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-01-18 17:12:00 +0800
 */

namespace Teddy\Snowflake;

use Godruoyi\Snowflake\SequenceResolver;
use Teddy\Redis\Redis;

class RedisSequenceResolver implements SequenceResolver
{
    protected $redis;

    protected $prefix;

    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public function sequence(int $currentTime)
    {
        $script = '
            if redis.call("EXISTS", KEYS[1]) > 0 then
                return false
            else
                return redis.call("PSETEX", KEYS[1], ARGV[2], ARGV[1])
            end
        ';

        $key = $this->prefix . $currentTime;
        if ($this->redis->runCommand('eval', [
            $script,
            [$key, 1, 1000],
            1,
        ])) {
            return 0;
        }

        return $this->redis->runCommand('incrBy', [$key, 1]);
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }
}
