<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-01-18 16:51:58 +0800
 */

namespace Teddy\Snowflake;

use Godruoyi\Snowflake\Snowflake;

class Manager extends Snowflake
{
    public function __construct()
    {
        $config = config('snowflake');
        $dataCenter = intval($config['dataCenter'] ?? 0);
        $workId = intval($config['workId'] ?? 0);
        parent::__construct($dataCenter, $workId);

        if (isset($config['redis'])) {
            $redisConfig = (array) $config['redis'] ?? [];
            $connection = $redisConfig['connection'] ?? null;
            $resolver = new RedisSequenceResolver(app('redis')->connection($connection));
            $resolver->setPrefix($redisConfig['prefix'] ?? '');
            $this->setSequenceResolver($resolver);
        } elseif (isset($config['swoole'])) {
            $this->setSequenceResolver(new SwooleSequenceResolver);
        }
    }
}
