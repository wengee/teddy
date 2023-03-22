<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 17:01:08 +0800
 */

namespace Teddy\Pool;

use Teddy\Config\Repository;
use Teddy\Interfaces\ConnectionInterface;
use Teddy\Runtime;
use Throwable;

abstract class Pool
{
    protected ?Channel $channel = null;

    protected ?array $poolOptions = null;

    protected int $currentConnections = 0;

    protected ?ConnectionInterface $instance = null;

    /**
     * @param null|array|bool $options
     */
    public function __construct($options = null)
    {
        if (is_array($options) || (true === $options) || Runtime::isSwoole()) {
            $this->poolOptions = (new Repository([
                'minConnections' => 1,
                'maxConnections' => 10,
                'connectTimeout' => 10.0,
                'waitTimeout'    => 3.0,
                'heartbeat'      => 0,
                'maxIdleTime'    => 900,
            ]))->merge(is_array($options) ? $options : [])->toArray();

            $this->channel = new Channel($this->poolOptions['maxConnections']);
        }
    }

    public function getConnection(): ConnectionInterface
    {
        if (!$this->poolOptions) {
            if (!$this->instance) {
                $this->instance = $this->createConnection();
            }

            return $this->instance;
        }

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

    public function releaseConnection(ConnectionInterface $connection): void
    {
        if (!$this->poolOptions) {
            return;
        }

        $this->channel->push($connection);
    }

    public function flushConnections(): void
    {
        if (!$this->poolOptions) {
            return;
        }

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
        if (!$this->poolOptions) {
            return $this->instance ? 1 : 0;
        }

        return $this->channel->length();
    }

    abstract protected function createConnection(): ConnectionInterface;
}
