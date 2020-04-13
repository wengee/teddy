<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-04-13 15:04:30 +0800
 */

namespace Teddy\Snowflake;

use Godruoyi\Snowflake\Snowflake;
use Teddy\Interfaces\SnowflakeInterface;

class Manager implements SnowflakeInterface
{
    protected $snowflake;

    public function __construct()
    {
        $config = config('snowflake');
        $dataCenter = intval($config['dataCenter'] ?? -1);
        $workerId = intval($config['workerId'] ?? -1);
        $this->snowflake = new Snowflake($dataCenter, $workerId);

        $startTimestamp = intval($config['startTimestamp'] ?? -1);
        if ($startTimestamp > 0) {
            $this->snowflake->setStartTimeStamp($startTimestamp);
        }

        $sequenceResolver = $config['sequenceResolver'] ?? null;
        if ($sequenceResolver === 'redis') {
            $redisConfig = (array) $config['redis'] ?? [];
            $connection = $redisConfig['connection'] ?? null;
            $resolver = new RedisSequenceResolver(app('redis')->connection($connection));
            $resolver->setPrefix($redisConfig['prefix'] ?? '');
            $this->snowflake->setSequenceResolver($resolver);
        } elseif ($sequenceResolver === 'swoole') {
            $this->snowflake->setSequenceResolver(new SwooleSequenceResolver);
        }
    }

    public function id(): int
    {
        return (int) $this->snowflake->id();
    }

    public function parseId(int $id, bool $transform = false): array
    {
        return $this->snowflake->parseId(strval($id), $transform);
    }

    public function setSequenceResolver($resolver): void
    {
        $this->snowflake->setSequenceResolver($resolver);
    }

    public function getSequenceResolver()
    {
        return $this->snowflake->getSequenceResolver();
    }
}
