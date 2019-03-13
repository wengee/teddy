<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-10 14:53:34 +0800
 */
namespace Teddy\Redis;

class Redis extends \Redis
{
    /**
     * @var string
     */
    protected $connection;

    public function setConnection(?string $connection = null)
    {
        $this->connection = $connection;
    }

    public function getConnection(): ?string
    {
        return $this->connection;
    }
}
