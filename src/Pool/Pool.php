<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-30 09:44:52 +0800
 */

namespace Teddy\Pool;

use Teddy\Config\Repository;
use Teddy\Interfaces\ConnectionInterface;
use Throwable;

abstract class Pool
{
    /** @var Channel */
    protected $channel;

    /** @var null|array|bool */
    protected $poolOptions = false;

    /** @var int */
    protected $currentConnections = 0;

    /** @var null|ConnectionInterface */
    protected $instance;

    public function __construct(array|null|bool $options = [])
    {
        if (is_array($options) || ($options === true)) {
            $this->poolOptions = (new Repository([
                'minConnections' => 1,
                'maxConnections' => 10,
                'connectTimeout' => 10.0,
                'waitTimeout'    => 3.0,
                'heartbeat'      => 0,
                'maxIdleTime'    => 900,
            ]))->merge($options ?: [])->toArray();

            $this->channel = new Channel($this->poolOptions['maxConnections']);
        }
    }

    public function get(): ConnectionInterface
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

        return $this->channel->pop();
    }

    public function release(ConnectionInterface $connection): void
    {
        if (!$this->poolOptions) {
            return;
        }

        $this->channel->push($connection);
    }

    public function flush(): void
    {
        if (!$this->poolOptions) {
            return;
        }

        $num = $this->getConnectionsInChannel();

        if ($num > 0) {
            while ($this->currentConnections > $this->poolOptions['minConnections'] && $conn = $this->channel->pop()) {
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
