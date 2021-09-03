<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Database\Schema;

use Closure;
use LogicException;
use Teddy\Database\PDOConnection;

class Builder
{
    /**
     * The default string length for migrations.
     *
     * @var int
     */
    public static $defaultStringLength = 255;
    protected static $callback;

    protected $connection;

    protected $grammar;

    /**
     * The Blueprint resolver callback.
     *
     * @var \Closure
     */
    protected $resolver;

    public function __construct(PDOConnection $connection)
    {
        $this->connection = $connection;
        $this->grammar    = $connection->getSchemaGrammar();
    }

    public static function defaultStringLength(int $length): void
    {
        static::$defaultStringLength = $length;
    }

    public function callback(?callable $callback): void
    {
        self::$callback = $callback;
    }

    public function hasTable(string $table): bool
    {
        $table = $this->connection->getTablePrefix().$table;

        return count($this->connection->select(
            $this->grammar->compileTableExists(),
            [$table]
        )) > 0;
    }

    public function hasColumn(string $table, string $column): bool
    {
        return in_array(
            strtolower($column),
            array_map('strtolower', $this->getColumnListing($table))
        );
    }

    public function hasColumns(string $table, array $columns): bool
    {
        $tableColumns = array_map('strtolower', $this->getColumnListing($table));

        foreach ($columns as $column) {
            if (!in_array(strtolower($column), $tableColumns)) {
                return false;
            }
        }

        return true;
    }

    public function getColumnListing(string $table): array
    {
        return $this->connection->select($this->grammar->compileColumnListing(
            $this->connection->getTablePrefix().$table
        ));
    }

    public function table(string $table, Closure $callback)
    {
        return $this->build($this->createBlueprint($table, $callback));
    }

    public function create(string $table, Closure $callback)
    {
        return $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback): void {
            $blueprint->create();
            $callback($blueprint);
        }));
    }

    public function drop(string $table)
    {
        return $this->build(tap($this->createBlueprint($table), function ($blueprint): void {
            $blueprint->drop();
        }));
    }

    public function dropIfExists(string $table)
    {
        return $this->build(tap($this->createBlueprint($table), function ($blueprint): void {
            $blueprint->dropIfExists();
        }));
    }

    public function dropAllTables(): void
    {
        throw new LogicException('This database driver does not support dropping all tables.');
    }

    public function dropAllViews(): void
    {
        throw new LogicException('This database driver does not support dropping all views.');
    }

    public function rename(string $from, string $to)
    {
        return $this->build(tap($this->createBlueprint($from), function ($blueprint) use ($to): void {
            $blueprint->rename($to);
        }));
    }

    public function enableForeignKeyConstraints()
    {
        return $this->connection->query(
            $this->grammar->compileEnableForeignKeyConstraints()
        );
    }

    public function disableForeignKeyConstraints()
    {
        return $this->connection->query(
            $this->grammar->compileDisableForeignKeyConstraints()
        );
    }

    public function getConnection(): PDOConnection
    {
        return $this->connection;
    }

    public function setConnection(PDOConnection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    public function blueprintResolver(Closure $resolver): void
    {
        $this->resolver = $resolver;
    }

    protected function build(Blueprint $blueprint): void
    {
        if (!self::$callback) {
            $blueprint->build($this->connection, $this->grammar);
        } else {
            $sql = $blueprint->toSql($this->connection, $this->grammar);
            call_user_func(self::$callback, $sql);
        }
    }

    protected function createBlueprint(string $table, Closure $callback = null)
    {
        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback);
        }

        return new Blueprint($table, $callback);
    }
}
