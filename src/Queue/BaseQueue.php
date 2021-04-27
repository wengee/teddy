<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 16:42:42 +0800
 */

namespace Teddy\Queue;

class BaseQueue
{
    protected $key;

    protected $channelKey;

    protected $redis;

    public function __construct(array $config)
    {
        $this->key         = $config['key'] ?? 'task:queue';
        $this->channelKey  = $this->key.':channel';
        $this->redis       = $config['redis'] ?? 'default';
    }
}
