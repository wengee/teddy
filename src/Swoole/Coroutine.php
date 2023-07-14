<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-07-14 17:18:31 +0800
 */

namespace Teddy\Swoole;

use ArrayObject;
use RuntimeException;
use Swoole\Coroutine as SwooleCo;

class Coroutine
{
    private $callable;

    private ?int $id;

    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    public static function create(callable $callable, ...$args)
    {
        $coroutine = new static($callable);
        $coroutine->execute(...$args);

        return $coroutine;
    }

    public function execute(...$args): static
    {
        $this->id = SwooleCo::create($this->callable, ...$args);

        return $this;
    }

    public function getId(): int
    {
        if (null === $this->id) {
            throw new RuntimeException('Coroutine was not be executed.');
        }

        return $this->id;
    }

    public static function id(): int
    {
        return SwooleCo::getCid();
    }

    public static function pid(?int $id = null): int
    {
        if ($id) {
            $cid = SwooleCo::getPcid($id);
            if (false === $cid) {
                throw new RuntimeException(sprintf('Coroutine #%d has been destroyed.', $id));
            }
        } else {
            $cid = SwooleCo::getPcid();
        }
        if (false === $cid) {
            throw new RuntimeException('Non-Coroutine environment don\'t has parent coroutine id.');
        }

        return max(0, $cid);
    }

    public static function set(array $config): void
    {
        SwooleCo::set($config);
    }

    public static function getContextFor(?int $id = null): ?ArrayObject
    {
        if (null === $id) {
            return SwooleCo::getContext();
        }

        return SwooleCo::getContext($id);
    }

    public static function defer(callable $callable): void
    {
        SwooleCo::defer($callable);
    }

    /**
     * Yield the current coroutine.
     *
     * @param mixed $data only Support Swow
     *
     * @return bool
     */
    public static function yield(mixed $data = null): mixed
    {
        return SwooleCo::yield();
    }

    /**
     * Resume the coroutine by coroutine Id.
     *
     * @param mixed $data only Support Swow
     *
     * @return bool
     */
    public static function resumeById(int $id, mixed ...$data): mixed
    {
        return SwooleCo::resume($id);
    }

    /**
     * Get the coroutine stats.
     */
    public static function stats(): array
    {
        return SwooleCo::stats();
    }

    public static function exists(int $id = null): bool
    {
        return SwooleCo::exists($id);
    }
}
