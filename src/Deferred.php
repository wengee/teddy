<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-10 11:12:30 +0800
 */

namespace Teddy;

use Swoole\Coroutine;

class Deferred
{
    /**
     * @var null|callable
     */
    protected $callback;

    /**
     * @var bool
     */
    protected $executed = false;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
        if (defined('IN_SWOOLE') && IN_SWOOLE) {
            Coroutine::defer($callback);
        }
    }

    public function __destruct()
    {
        $this->run();
    }

    public static function create(callable $callback): self
    {
        return new self($callback);
    }

    public function run(): void
    {
        if ((!defined('IN_SWOOLE') || !IN_SWOOLE) && !$this->executed && $this->callback) {
            $this->executed = true;
            safe_call($this->callback);
        }
    }
}
