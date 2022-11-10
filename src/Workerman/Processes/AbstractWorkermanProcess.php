<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 16:28:56 +0800
 */

namespace Teddy\Workerman\Processes;

abstract class AbstractWorkermanProcess
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
