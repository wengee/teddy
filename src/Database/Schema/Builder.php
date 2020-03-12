<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-11 17:09:33 +0800
 */

namespace Teddy\Database\Schema;

use Closure;
use LogicException;
use Teddy\Database\DbConnectionInterface;

class Builder
{
    protected $connection;

    protected $grammar;

    /**
     * The Blueprint resolver callback.
     *
     * @var \Closure
     */
    protected $resolver;

    /**
     * The default string length for migrations.
     *
     * @var int
     */
    public static $defaultStringLength = 255;

    public function __construct(DbConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->grammar = $connection->getSchemaGrammar();
    }

    public static function defaultStringLength(int $length): void
    {
        static::$defaultStringLength = $length;
    }

    public function hasTable(string $table): bool
    {
        $table = $this->connection->getTablePrefix() . $table;

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
            $this->connection->getTablePrefix() . $table
        ));
    }

    public function table(string $table, Closure $callback): void
    {
        $this->build($this->createBlueprint($table, $callback));
    }

    public function create(string $table, Closure $callback): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback): void {
            $blueprint->create();
            $callback($blueprint);
        }));
    }

    public function drop(string $table): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint): void {
            $blueprint->drop();
        }));
    }

    public function dropIfExists(string $table): void
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint): void {
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

    public function rename(string $from, string $to): void
    {
        $this->build(tap($this->createBlueprint($from), function ($blueprint) use ($to): void {
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

    protected function build(Blueprint $blueprint): void
    {
        $blueprint->build($this->connection, $this->grammar);
    }

    protected function createBlueprint(string $table, Closure $callback = null)
    {
        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback);
        }

        return new Blueprint($table, $callback);
    }

    public function getConnection(): DbConnectionInterface
    {
        return $this->connection;
    }

    public function setConnection(DbConnectionInterface $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    public function blueprintResolver(Closure $resolver): void
    {
        $this->resolver = $resolver;
    }
}
