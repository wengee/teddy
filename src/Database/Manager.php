<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-15 17:09:51 +0800
 */

namespace Teddy\Database;

use Psr\Log\LoggerInterface;
use Teddy\Exception;
use Teddy\Interfaces\ContainerInterface;
use Teddy\Interfaces\WithContainerInterface;

class Manager implements WithContainerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var array
     */
    protected $pools = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $config = config('database');
        if ($config && is_array($config)) {
            $this->config = $config;

            $loggerChannel = $config['logger'] ?? null;
            if ($loggerChannel) {
                $this->logger = $this->container->get('logger')->channel($loggerChannel);
            }
        }
    }

    public function __call(string $method, array $args)
    {
        $connection = $this->connection();

        return $connection->{$method}(...$args);
    }

    public function connection(?string $key = null): Database
    {
        $key = $key ?: 'default';
        if (!isset($this->pools[$key])) {
            if (!isset($this->config[$key]) || !is_array($this->config[$key])) {
                throw new Exception('Can not found the database config.');
            }

            $database = new Database($this->config[$key]);
            if ($this->logger) {
                $database->setLogger($this->logger);
            }

            $this->pools[$key] = $database;
        }

        return $this->pools[$key];
    }
}
