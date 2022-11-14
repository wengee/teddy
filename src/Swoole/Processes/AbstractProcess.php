<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 21:03:25 +0800
 */

namespace Teddy\Swoole\Processes;

use Teddy\Swoole\ProcessInterface as SwooleProcessInterface;
use Teddy\Utils\Process;

abstract class AbstractProcess implements SwooleProcessInterface
{
    /**
     * @var bool
     */
    protected $isPool = false;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var int
     */
    protected $count = 1;

    /**
     * @var bool
     */
    protected $enableCoroutine = false;

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var int
     */
    protected $port = 0;

    /**
     * @var bool
     */
    protected $useSSL = false;

    /**
     * @var bool
     */
    protected $reusePort = true;

    /**
     * @var array
     */
    protected $options = [];

    public function isPool(): bool
    {
        return $this->isPool;
    }

    public function getName(): string
    {
        return $this->name.' process';
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function enableCoroutine(): bool
    {
        return $this->enableCoroutine;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getListen(): string
    {
        if ($this->host && $this->port) {
            return $this->host.':'.$this->port;
        }

        return 'none';
    }

    public function start(int $workerId): void
    {
        Process::setTitle($this->getName());
        $this->handle($workerId);
    }

    abstract public function handle(int $workerId);
}
