<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 16:54:56 +0800
 */

namespace Teddy\Abstracts;

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
     * @var int
     */
    protected $count = 1;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var Worker
     */
    protected $worker;

    public function getName(): string
    {
        return $this->name ?: ('custom-'.(++self::$orderNum));
    }

    public function getCount(): int
    {
        return $this->count;
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
     * @param Worker $worker
     */
    public function setWorker($worker): void
    {
        $this->worker = $worker;
    }

    /**
     * @return Worker
     */
    public function getWorker()
    {
        return $this->worker;
    }

    abstract public function handle();

    abstract public function onReload();
}
