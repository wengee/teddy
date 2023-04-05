<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-05 10:12:02 +0800
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

        return null;
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
