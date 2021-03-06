<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 16:27:09 +0800
 */

namespace Teddy\Pool;

use Teddy\Interfaces\ConnectionInterface;
use Teddy\Options;
use Throwable;

abstract class Pool
{
    protected $channel;

    protected $poolOptions;

    protected $currentConnections = 0;

    public function __construct(array $options = [])
    {
        $this->poolOptions = new Options([
            'minConnections' => 1,
            'maxConnections' => 10,
            'connectTimeout' => 10.0,
            'waitTimeout'    => 3.0,
            'heartbeat'      => 0,
            'maxIdleTime'    => 900,
        ]);
        $this->poolOptions->update($options);
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
