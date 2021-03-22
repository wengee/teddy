<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-03-22 15:41:06 +0800
 */

namespace Teddy\Database\Schema\Grammars;

use Illuminate\Support\Fluent;
use Teddy\Database\DbConnectionInterface;
use Teddy\Database\Schema\Blueprint;

class MysqlGrammar extends Grammar
{
    /**
     * The possible column modifiers.
     *
     * @var array
     */
    protected $modifiers = [
        'Unsigned', 'VirtualAs', 'StoredAs', 'Charset', 'Collate', 'Nullable',
        'Default', 'Increment', 'Comment', 'After', 'First', 'Srid',
    ];

    /**
     * The possible column serials.
     *
     * @var array
     */
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];

    /**
     * Compile the query to determine the list of tables.
     */
    public function compileTableExists(): string
    {
        return 'SELECT * FROM `information_schema`.`tables` WHERE `table_schema` = ? AND `table_name` = ?';
    }

    /**
     * Compile the query to determine the list of columns.
     */
    public function compileColumnListing(): string
    {
        return 'SELECT `column_name` FROM `information_schema`.`columns` WHERE `table_schema` = ? AND `table_name` = ?';
    }

    public function compileCreate(Blueprint $blueprint, Fluent $command, DbConnectionInterface $connection): string
    {
        $sql = $this->compileCreateTable($blueprint, $command, $connection);
        $sql = $this->compileCreateEncoding($sql, $connection, $blueprint);

        return $this->compileCreateEngine($sql, $connection, $blueprint);
    }

    public function compileAdd(Blueprint $blueprint, Fluent $command): string
    {
        $columns = $this->prefixArray('ADD', $this->getColumns($blueprint));

        return 'ALTER TABLE '.$this->wrapTable($blueprint).' '.implode(', ', $columns);
    }

    public function compilePrimary(Blueprint $blueprint, Fluent $command): string
    {
        $command->name(null);

        return $this->compileKey($blueprint, $command, 'PRIMARY KEY');
    }

    public function compileUnique(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileKey($blueprint, $command, 'UNIQUE');
    }

    public function compileIndex(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileKey($blueprint, $command, 'INDEX');
    }

    public function compileSpatialIndex(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileKey($blueprint, $command, 'SPATIAL INDEX');
    }

    public function compileFullTextIndex(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileKey($blueprint, $command, 'FULLTEXT INDEX');
    }

    public function compileDrop(Blueprint $blueprint, Fluent $command): string
    {
        return 'DROP TABLE '.$this->wrapTable($blueprint);
    }

    public function compileDropIfExists(Blueprint $blueprint, Fluent $command): string
    {
        return 'DROP TABLE IF EXISTS '.$this->wrapTable($blueprint);
    }

    public function compileDropColumn(Blueprint $blueprint, Fluent $command): string
    {
        $columns = $this->prefixArray('DROP', $this->wrapArray($command->columns));

        return 'ALTER TABLE '.$this->wrapTable($blueprint).' '.implode(', ', $columns);
    }

    public function compileDropPrimary(Blueprint $blueprint, Fluent $command): string
    {
        return 'ALTER TABLE '.$this->wrapTable($blueprint).' DROP PRIMARY KEY';
    }

    public function compileDropUnique(Blueprint $blueprint, Fluent $command): string
    {
        $index = $this->wrap($command->index);

        return "ALTER TABLE {$this->wrapTable($blueprint)} DROP INDEX {$index}";
    }

    public function compileDropIndex(Blueprint $blueprint, Fluent $command): string
    {
        $index = $this->wrap($command->index);

        return "ALTER TABLE {$this->wrapTable($blueprint)} DROP INDEX {$index}";
    }

    public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command): string
    {
        return $this->compileDropIndex($blueprint, $command);
    }

    public function compileDropForeign(Blueprint $blueprint, Fluent $command): string
    {
        $index = $this->wrap($command->index);

        return "ALTER TABLE {$this->wrapTable($blueprint)} DROP FOREIGN KEY {$index}";
    }

    public function compileRename(Blueprint $blueprint, Fluent $command): string
    {
        $from = $this->wrapTable($blueprint);

        return "RENAME TABLE {$from} TO ".$this->wrapTable($command->to);
    }

    public function compileRenameIndex(Blueprint $blueprint, Fluent $command): string
    {
        return sprintf(
            'ALTER TABLE %s RENAME INDEX %s TO %s',
            $this->wrapTable($blueprint),
            $this->wrap($command->from),
            $this->wrap($command->to)
        );
    }

    public function compileDropAllTables(array $tables): string
    {
        return 'DROP TABLE '.implode(',', $this->wrapArray($tables));
    }

    public function compileDropAllViews(array $views): string
    {
        return 'DROP VIEW '.implode(',', $this->wrapArray($views));
    }

    public function compileGetAllTables(): string
    {
        return 'SHOW FULL TABLES WHERE `table_type` = \'BASE TABLE\'';
    }

    public function compileGetAllViews(): string
    {
        return 'SHOW FULL TABLES WHERE `table_type` = \'VIEW\'';
    }

    public function compileEnableForeignKeyConstraints(): string
    {
        return 'SET FOREIGN_KEY_CHECKS = 1;';
    }

    public function compileDisableForeignKeyConstraints(): string
    {
        return 'SET FOREIGN_KEY_CHECKS = 0;';
    }

    public function typeGeometry(Fluent $column): string
    {
        return 'GEOMETRY';
    }

    public function typePoint(Fluent $column): string
    {
        return 'POINT';
    }

    public function typeLineString(Fluent $column): string
    {
        return 'LINESTRING';
    }

    public function typePolygon(Fluent $column): string
    {
        return 'POLYGON';
    }

    public function typeGeometryCollection(Fluent $column): string
    {
        return 'GEOMETRYCOLLECTION';
    }

    public function typeMultiPoint(Fluent $column): string
    {
        return 'MULTIPOINT';
    }

    public function typeMultiLineString(Fluent $column): string
    {
        return 'MULTILINESTRING';
    }

    public function typeMultiPolygon(Fluent $column): string
    {
        return 'MULTIPOLYGON';
    }

    protected function compileCreateTable(Blueprint $blueprint, Fluent $command, DbConnectionInterface $connection): string
    {
        return sprintf(
            '%s TABLE %s (%s)',
            $blueprint->temporary ? 'CREATE TEMPORARY' : 'CREATE',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint))
        );
    }

    protected function compileCreateEncoding(string $sql, DbConnectionInterface $connection, Blueprint $blueprint): string
    {
        if (isset($blueprint->charset)) {
            $sql .= ' DEFAULT CHARACTER SET '.$blueprint->charset;
        } elseif (!is_null($charset = $connection->getConfig('charset'))) {
            $sql .= ' DEFAULT CHARACTER SET '.$charset;
        }

        if (isset($blueprint->collation)) {
            $sql .= " COLLATE '{$blueprint->collation}'";
        } elseif (!is_null($collation = $connection->getConfig('collation'))) {
            $sql .= " COLLATE '{$collation}'";
        }

        return $sql;
    }

    protected function compileCreateEngine(string $sql, DbConnectionInterface $connection, Blueprint $blueprint): string
    {
        if (isset($blueprint->engine)) {
            return $sql.' ENGINE = '.$blueprint->engine;
        }
        if (!is_null($engine = $connection->getConfig('engine'))) {
            return $sql.' ENGINE = '.$engine;
        }

        return $sql;
    }

    protected function compileKey(Blueprint $blueprint, Fluent $command, string $type): string
    {
        return sprintf(
            'ALTER TABLE %s ADD %s %s%s(%s)',
            $this->wrapTable($blueprint),
            $type,
            $this->wrap($command->index),
            $command->algorithm ? ' USING '.$command->algorithm : '',
            $this->columnize($command->columns)
        );
    }

    protected function typeChar(Fluent $column): string
    {
        return "CHAR({$column->length})";
    }

    protected function typeString(Fluent $column): string
    {
        return "VARCHAR({$column->length})";
    }

    protected function typeText(Fluent $column): string
    {
        return 'TEXT';
    }

    protected function typeMediumText(Fluent $column): string
    {
        return 'MEDIUMTEXT';
    }

    protected function typeLongText(Fluent $column): string
    {
        return 'LONGTEXT';
    }

    protected function typeBigInteger(Fluent $column): string
    {
        return 'BIGINT';
    }

    protected function typeInteger(Fluent $column): string
    {
        return 'INT';
    }

    protected function typeMediumInteger(Fluent $column): string
    {
        return 'MEDIUMINT';
    }

    protected function typeTinyInteger(Fluent $column): string
    {
        return 'TINYINT';
    }

    protected function typeSmallInteger(Fluent $column): string
    {
        return 'SMALLINT';
    }

    protected function typeFloat(Fluent $column): string
    {
        return $this->typeDouble($column);
    }

    protected function typeDouble(Fluent $column): string
    {
        if ($column->total && $column->places) {
            return "DOUBLE({$column->total}, {$column->places})";
        }

        return 'DOUBLE';
    }

    protected function typeDecimal(Fluent $column): string
    {
        return "DECIMAL({$column->total}, {$column->places})";
    }

    protected function typeBoolean(Fluent $column): string
    {
        return 'TINYINT(1)';
    }

    protected function typeEnum(Fluent $column): string
    {
        return sprintf('ENUM(%s)', $this->quoteString($column->allowed));
    }

    protected function typeJson(Fluent $column): string
    {
        return 'JSON';
    }

    protected function typeJsonb(Fluent $column): string
    {
        return 'JSON';
    }

    protected function typeDate(Fluent $column): string
    {
        return 'DATE';
    }

    protected function typeDateTime(Fluent $column): string
    {
        return $column->precision ? "DATETIME({$column->precision})" : 'DATETIME';
    }

    protected function typeTime(Fluent $column): string
    {
        return $column->precision ? "TIME({$column->precision})" : 'TIME';
    }

    protected function typeTimestamp(Fluent $column): string
    {
        $columnType = $column->precision ? "TIMESTAMP({$column->precision})" : 'TIMESTAMP';

        return $column->useCurrent ? "{$columnType} DEFAULT CURRENT_TIMESTAMP" : $columnType;
    }

    protected function typeYear(Fluent $column): string
    {
        return 'YEAR';
    }

    protected function typeBinary(Fluent $column): string
    {
        return 'BLOB';
    }

    protected function typeUuid(Fluent $column): string
    {
        return 'CHAR(36)';
    }

    protected function typeIpAddress(Fluent $column): string
    {
        return 'VARCHAR(45)';
    }

    protected function typeMacAddress(Fluent $column): string
    {
        return 'VARCHAR(17)';
    }

    protected function modifyVirtualAs(Blueprint $blueprint, Fluent $column): ?string
    {
        if (!is_null($column->virtualAs)) {
            return " AS ({$column->virtualAs})";
        }

        return null;
    }

    protected function modifyStoredAs(Blueprint $blueprint, Fluent $column): ?string
    {
        if (!is_null($column->storedAs)) {
            return " AS ({$column->storedAs}) STORED";
        }

        return null;
    }

    protected function modifyUnsigned(Blueprint $blueprint, Fluent $column): ?string
    {
        if ($column->unsigned) {
            return ' UNSIGNED';
        }

        return null;
    }

    protected function modifyCharset(Blueprint $blueprint, Fluent $column): ?string
    {
        if (!is_null($column->charset)) {
            return ' CHARACTER SET '.$column->charset;
        }

        return null;
    }

    protected function modifyCollate(Blueprint $blueprint, Fluent $column): ?string
    {
        if (!is_null($column->collation)) {
            return " COLLATE '{$column->collation}'";
        }

        return null;
    }

    protected function modifyNullable(Blueprint $blueprint, Fluent $column): ?string
    {
        if (is_null($column->virtualAs) && is_null($column->storedAs)) {
            return $column->nullable ? ' NULL' : ' NOT NULL';
        }

        return null;
    }

    protected function modifyDefault(Blueprint $blueprint, Fluent $column): ?string
    {
        if (!is_null($column->default)) {
            return ' DEFAULT '.$this->getDefaultValue($column->default);
        }

        return null;
    }

    protected function modifyIncrement(Blueprint $blueprint, Fluent $column): ?string
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' AUTO_INCREMENT PRIMARY KEY';
        }

        return null;
    }

    protected function modifyFirst(Blueprint $blueprint, Fluent $column): ?string
    {
        if (!is_null($column->first)) {
            return ' FIRST';
        }

        return null;
    }

    protected function modifyAfter(Blueprint $blueprint, Fluent $column): ?string
    {
        if (!is_null($column->after)) {
            return ' AFTER '.$this->wrap($column->after);
        }

        return null;
    }

    protected function modifyComment(Blueprint $blueprint, Fluent $column): ?string
    {
        if (!is_null($column->comment)) {
            return " COMMENT '".addslashes($column->comment)."'";
        }

        return null;
    }

    protected function modifySrid(Blueprint $blueprint, Fluent $column): ?string
    {
        if (!is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return ' SRID '.$column->srid;
        }

        return null;
    }

    protected function wrapValue(string $value): string
    {
        if ('*' !== $value) {
            return '`'.str_replace('.', '`.`', $value).'`';
        }

        return '`'.$value.'`';
    }
}
