<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-08 15:55:39 +0800
 */
namespace Teddy\Pool;

use Teddy\Interfaces\ConnectionInterface;
use Teddy\Options;
use Throwable;

abstract class Pool
{
    protected $channel;

    protected $options;

    protected $currentConnections = 0;

    public function __construct(array $options = [])
    {
        $this->options = (new Options([
            'minConnections' => 1,
            'maxConnections' => 10,
            'connectTimeout' => 10.0,
            'waitTimeout' => 3.0,
            'heartbeat' => 0,
            'maxIdleTime' => 900,
        ], true))->update($options);

        $this->channel = new Channel($this->options['maxConnections']);
    }

    public function get(): ConnectionInterface
    {
        $num = $this->getConnectionsInChannel();

        try {
            if ($num === 0 && $this->currentConnections < $this->options['maxConnections']) {
                ++$this->currentConnections;
                return $this->createConnection();
            }
        } catch (Throwable $throwable) {
            --$this->currentConnections;
            throw $throwable;
        }

        return $this->channel->pop($this->options['waitTimeout']);
    }

    public function release(ConnectionInterface $connection): void
    {
        $this->channel->push($connection);
    }

    public function flush(): void
    {
        $num = $this->getConnectionsInChannel();

        if ($num > 0) {
            while ($this->currentConnections > $this->options['minConnections'] && $conn = $this->channel->pop($this->options['waitTimeout'])) {
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
