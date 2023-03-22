<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:46:39 +0800
 */

namespace Teddy\Database\Schema\Grammars;

use Illuminate\Support\Fluent;
use Teddy\Database\Grammar as BaseGrammar;
use Teddy\Database\PDOConnection;
use Teddy\Database\Schema\Blueprint;

abstract class Grammar extends BaseGrammar
{
    /**
     * If this Grammar supports schema changes wrapped in a transaction.
     */
    protected bool $transactions = false;

    /**
     * The commands to be executed outside of create or alter command.
     */
    protected array $fluentCommands = [];

    protected array $modifiers = [];

    public function compileRenameColumn(Blueprint $blueprint, Fluent $command, PDOConnection $connection)
    {
        return RenameColumn::compile($this, $blueprint, $command, $connection);
    }

    public function compileChange(Blueprint $blueprint, Fluent $command, PDOConnection $connection)
    {
        return ChangeColumn::compile($this, $blueprint, $command, $connection);
    }

    public function compileForeign(Blueprint $blueprint, Fluent $command): string
    {
        // We need to prepare several of the elements of the foreign key definition
        // before we can create the SQL, such as wrapping the tables and convert
        // an array of columns to comma-delimited strings for the SQL queries.
        $sql = sprintf(
            'alter table %s add constraint %s ',
            $this->wrapTable($blueprint),
            $this->wrap($command->index)
        );

        // Once we have the initial portion of the SQL statement we will add on the
        // key name, table name, and referenced columns. These will complete the
        // main portion of the SQL statement and this SQL will almost be done.
        $sql .= sprintf(
            'foreign key (%s) references %s (%s)',
            $this->columnize($command->columns),
            $this->wrapTable($command->on),
            $this->columnize((array) $command->references)
        );

        // Once we have the basic foreign key creation statement constructed we can
        // build out the syntax for what should happen on an update or delete of
        // the affected columns, which will get something like "cascade", etc.
        if (!is_null($command->onDelete)) {
            $sql .= " on delete {$command->onDelete}";
        }

        if (!is_null($command->onUpdate)) {
            $sql .= " on update {$command->onUpdate}";
        }

        return $sql;
    }

    public function prefixArray($prefix, array $values): array
    {
        return array_map(function ($value) use ($prefix) {
            return $prefix.' '.$value;
        }, $values);
    }

    public function wrapTable($table): string
    {
        return parent::wrapTable($table instanceof Blueprint ? $table->getTable() : $table);
    }

    public function wrap($value): string
    {
        return parent::wrap($value instanceof Fluent ? $value->name : $value);
    }

    public function getFluentCommands(): array
    {
        return $this->fluentCommands;
    }

    public function supportsSchemaTransactions(): bool
    {
        return $this->transactions;
    }

    protected function getColumns(Blueprint $blueprint): array
    {
        $columns = [];

        foreach ($blueprint->getAddedColumns() as $column) {
            $sql       = $this->wrap($column).' '.$this->getType($column);
            $columns[] = $this->addModifiers($sql, $blueprint, $column);
        }

        return $columns;
    }

    protected function getType(Fluent $column): string
    {
        return $this->{'type'.ucfirst($column->type)}($column);
    }

    protected function addModifiers(string $sql, Blueprint $blueprint, Fluent $column): string
    {
        foreach ($this->modifiers as $modifier) {
            if (method_exists($this, $method = "modify{$modifier}")) {
                $sql .= $this->{$method}($blueprint, $column);
            }
        }

        return $sql;
    }

    protected function getCommandByName(Blueprint $blueprint, string $name): ?Fluent
    {
        $commands = $this->getCommandsByName($blueprint, $name);

        if (count($commands) > 0) {
            return reset($commands);
        }
    }

    protected function getCommandsByName(Blueprint $blueprint, string $name): array
    {
        return array_filter($blueprint->getCommands(), function ($value) use ($name) {
            return $value->name == $name;
        });
    }

    protected function getDefaultValue($value): string
    {
        return is_bool($value) ?
            "'".(int) $value."'" :
            "'".(string) $value."'";
    }
}
