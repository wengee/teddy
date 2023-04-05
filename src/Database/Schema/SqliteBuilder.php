<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-05 09:37:53 +0800
 */

namespace Teddy\Database\Schema;

class SqliteBuilder extends Builder
{
    public function hasTable(string $table): bool
    {
        $table = $this->connection->getTablePrefix().$table;

        return count($this->connection->select(
            $this->grammar->compileTableExists(),
            [$this->connection->getDatabaseName(), $table]
        )) > 0;
    }

    public function getColumnListing(string $table): array
    {
        $table = $this->connection->getTablePrefix().$table;

        $results = $this->connection->select(
            $this->grammar->compileColumnListing(),
            [$this->connection->getDatabaseName(), $table]
        );

        return array_map(function ($result) {
            return ((object) $result)->column_name;
        }, $results);
    }

    public function dropAllTables(): void
    {
        if (':memory:' !== $this->connection->getDatabaseName()) {
            $this->refreshDatabaseFile();

            return;
        }

        $this->connection->select($this->grammar->compileEnableWriteableSchema());
        $this->connection->select($this->grammar->compileDropAllTables());
        $this->connection->select($this->grammar->compileDisableWriteableSchema());
        $this->connection->select($this->grammar->compileRebuild());
    }

    public function dropAllViews(): void
    {
        $this->connection->select($this->grammar->compileEnableWriteableSchema());
        $this->connection->select($this->grammar->compileDropAllViews());
        $this->connection->select($this->grammar->compileDisableWriteableSchema());
        $this->connection->select($this->grammar->compileRebuild());
    }

    public function getAllTables(): array
    {
        return $this->connection->query(
            $this->grammar->compileGetAllTables()
        );
    }

    public function getAllViews(): array
    {
        return $this->connection->query(
            $this->grammar->compileGetAllViews()
        );
    }

    public function refreshDatabaseFile(): void
    {
        file_put_contents($this->connection->getDatabaseName(), '');
    }
}
