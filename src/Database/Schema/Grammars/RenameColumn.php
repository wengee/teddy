<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-03-22 17:42:11 +0800
 */

namespace Teddy\Database\Schema\Grammars;

use Doctrine\DBAL\Schema\AbstractSchemaManager as SchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\TableDiff;
use Illuminate\Support\Fluent;
use Teddy\Database\PDOConnection;
use Teddy\Database\Schema\Blueprint;

class RenameColumn
{
    /**
     * Compile a rename column command.
     *
     * @return array
     */
    public static function compile(Grammar $grammar, Blueprint $blueprint, Fluent $command, PDOConnection $connection)
    {
        $column = $connection->getDoctrineColumn(
            $grammar->getTablePrefix().$blueprint->getTable(),
            $command->from
        );

        $schema = $connection->getDoctrineSchemaManager();

        return (array) $schema->getDatabasePlatform()->getAlterTableSQL(static::getRenamedDiff(
            $grammar,
            $blueprint,
            $command,
            $column,
            $schema
        ));
    }

    /**
     * Get a new column instance with the new column name.
     *
     * @param \Hyperf\Database\Schema\Grammars\Grammar $grammar
     *
     * @return \Doctrine\DBAL\Schema\TableDiff
     */
    protected static function getRenamedDiff(Grammar $grammar, Blueprint $blueprint, Fluent $command, Column $column, SchemaManager $schema)
    {
        return static::setRenamedColumns(
            $grammar->getDoctrineTableDiff($blueprint, $schema),
            $command,
            $column
        );
    }

    /**
     * Set the renamed columns on the table diff.
     *
     * @return \Doctrine\DBAL\Schema\TableDiff
     */
    protected static function setRenamedColumns(TableDiff $tableDiff, Fluent $command, Column $column)
    {
        $tableDiff->renamedColumns = [
            $command->from => new Column($command->to, $column->getType(), self::getWritableColumnOptions($column)),
        ];

        return $tableDiff;
    }

    /**
     * Get the writable column options.
     *
     * @return array
     */
    private static function getWritableColumnOptions(Column $column)
    {
        return array_filter($column->toArray(), function (string $name) use ($column) {
            return method_exists($column, 'set'.$name);
        }, ARRAY_FILTER_USE_KEY);
    }
}
