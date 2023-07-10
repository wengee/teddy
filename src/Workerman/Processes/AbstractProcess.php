<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-07-10 16:46:36 +0800
 */

namespace Teddy\Workerman\Processes;

abstract class AbstractProcess
{
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

    public function getCount(): int
    {
        return (int) $this->options['count'] ?? 0;
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
}
