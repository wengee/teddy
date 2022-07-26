<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 10:42:41 +0800
 */

namespace Teddy\Abstracts;

use Teddy\Interfaces\ProcessInterface;

abstract class AbstractProcess implements ProcessInterface
{
    protected static int $orderNum = 0;

    protected ?string $name;

    protected string $listen = '';

    protected array $context = [];

    protected array $options = [];

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

    public function setWorker($worker): void
    {
        $this->worker = $worker;
    }

    public function getWorker()
    {
        return $this->worker;
    }
}
