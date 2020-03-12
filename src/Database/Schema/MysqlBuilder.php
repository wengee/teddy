<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-11 17:14:31 +0800
 */

namespace Teddy\Database\Schema;

class MysqlBuilder extends Builder
{
    public function hasTable(string $table): bool
    {
        $table = $this->connection->getTablePrefix() . $table;

        return count($this->connection->select(
            $this->grammar->compileTableExists(),
            [$this->connection->getDatabaseName(), $table]
        )) > 0;
    }

    public function getColumnListing(string $table): array
    {
        $table = $this->connection->getTablePrefix() . $table;

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
        $tables = [];

        foreach ($this->getAllTables() as $row) {
            $row = (array) $row;
            $tables[] = reset($row);
        }

        if (empty($tables)) {
            return;
        }

        $this->disableForeignKeyConstraints();

        $this->connection->query(
            $this->grammar->compileDropAllTables($tables)
        );

        $this->enableForeignKeyConstraints();
    }

    public function dropAllViews(): void
    {
        $views = [];

        foreach ($this->getAllViews() as $row) {
            $row = (array) $row;
            $views[] = reset($row);
        }

        if (empty($views)) {
            return;
        }

        $this->connection->query(
            $this->grammar->compileDropAllViews($views)
        );
    }

    protected function getAllTables(): array
    {
        return $this->connection->query(
            $this->grammar->compileGetAllTables()
        );
    }

    protected function getAllViews(): array
    {
        return $this->connection->query(
            $this->grammar->compileGetAllViews()
        );
    }
}
