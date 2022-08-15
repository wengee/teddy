<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-15 15:43:01 +0800
 */

namespace Teddy\Abstracts;

use Swoole\Process;
use Teddy\Interfaces\ProcessInterface;
use Workerman\Worker;

abstract class AbstractProcess implements ProcessInterface
{
    /**
     * @var int
     */
    protected static $orderNum = 0;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $listen = '';

    /**
     * @var array
     */
    protected $context = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var Process|Worker
     */
    protected $worker;

    public function getName(): string
    {
        return $this->name ?: ('Custom-'.(++self::$orderNum));
    }

    public function getListen(): string
    {
        return $this->listen;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * @param Process|Worker $worker
     */
    public function setWorker($worker): void
    {
        $this->worker = $worker;
    }

    /**
     * @return Process|Worker
     */
    public function getWorker()
    {
        return $this->worker;
    }
}
