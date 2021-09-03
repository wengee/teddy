<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Pool;

use Teddy\Config\Repository;
use Teddy\Interfaces\ConnectionInterface;
use Throwable;

abstract class Pool
{
    /** @var Channel */
    protected $channel;

    /** @var array */
    protected $poolOptions;

    /** @var int */
    protected $currentConnections = 0;

    public function __construct(array $options = [])
    {
        $this->poolOptions = (new Repository([
            'minConnections' => 1,
            'maxConnections' => 10,
            'connectTimeout' => 10.0,
            'waitTimeout'    => 3.0,
            'heartbeat'      => 0,
            'maxIdleTime'    => 900,
        ]))->merge($options)->toArray();

        $this->channel = new Channel($this->poolOptions['maxConnections']);
    }

    public function get(): ConnectionInterface
    {
        $num = $this->getConnectionsInChannel();

        try {
            if (0 === $num && $this->currentConnections < $this->poolOptions['maxConnections']) {
                ++$this->currentConnections;

                return $this->createConnection();
            }
        } catch (Throwable $throwable) {
            --$this->currentConnections;

            throw $throwable;
        }

        return $this->channel->pop($this->poolOptions['waitTimeout']);
    }

    public function release(ConnectionInterface $connection): void
    {
        $this->channel->push($connection);
    }

    public function flush(): void
    {
        $num = $this->getConnectionsInChannel();

        if ($num > 0) {
            while ($this->currentConnections > $this->poolOptions['minConnections'] && $conn = $this->channel->pop($this->poolOptions['waitTimeout'])) {
                $conn->close();
                --$this->currentConnections;
            }
        }
    }

    protected function getConnectionsInChannel(): int
    {
        return $this->channel->length();
    }

    abstract protected function createConnection(): ConnectionInterface;
}
