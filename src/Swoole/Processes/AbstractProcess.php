<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 23:53:47 +0800
 */

namespace Teddy\Swoole\Processes;

use Teddy\Interfaces\SwooleProcessInterface;
use Teddy\Swoole\Util;

abstract class AbstractProcess implements SwooleProcessInterface
{
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

    public function getName(): string
    {
        return $this->name;
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

    public function start(): void
    {
        $appName = config('app.name', 'Teddy App');
        Util::setProcessTitle($this->getName(), $appName);
        $this->handle();
    }

    abstract public function handle();
}
