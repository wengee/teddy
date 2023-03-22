<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 17:00:16 +0800
 */

namespace Teddy\Database;

use Psr\Log\LoggerInterface;
use Teddy\Exception;
use Teddy\Interfaces\ContainerInterface;

class DbManager
{
    protected ContainerInterface $container;

    protected array $config = [];

    protected array $pools = [];

    protected ?LoggerInterface $logger = null;

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
