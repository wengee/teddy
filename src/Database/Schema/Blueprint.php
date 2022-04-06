<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-04-06 17:05:39 +0800
 */

namespace Teddy\Database\Schema;

use Closure;
use Illuminate\Support\Fluent;
use Illuminate\Support\Traits\Macroable;
use Teddy\Database\DbConnectionInterface;
use Teddy\Database\Grammar;

/**
 * @method void   build(DbConnectionInterface $connection, Grammar $grammar)
 * @method array  toSql(DbConnectionInterface $connection, Grammar $grammar)
 * @method void   addFluentCommands(Grammar $grammar)
 * @method self   create()
 * @method self   modify()
 * @method self   drop()
 * @method self   dropIfExists()
 * @method self   dropColumn(string|string[] $columns)
 * @method self   renameColumn(string $from, string $to)
 * @method self   dropPrimary(null|string $index = null)
 * @method self   dropUnique(null|string $index)
 * @method self   dropIndex(null|string $index)
 * @method self   dropSpatialIndex(null|string $index)
 * @method self   dropForeign(null|string $index)
 * @method self   renameIndex(string $from, string $to)
 * @method void   dropTimestamps()
 * @method void   dropSoftDeletes()
 * @method self   rename(string $to)
 * @method self   primary(string|string[] $columns, ?string $name = null, ?string $algorithm = null)
 * @method self   unique(string|string[] $columns, ?string $name = null, ?string $algorithm = null)
 * @method self   index(string|string[] $columns, ?string $name = null, ?string $algorithm = null)
 * @method self   spatialIndex(string|string[] $columns, ?string $name = null)
 * @method self   fullTextIndex(string|string[] $columns, ?string $name = null)
 * @method self   foreign(string|string[] $columns, ?string $name = null)
 * @method self   increments(string $column)
 * @method self   tinyIncrements(string $column)
 * @method self   smallIncrements(string $column)
 * @method self   mediumIncrements(string $column)
 * @method self   bigIncrements(string $column)
 * @method self   char(string $column, ?int $length = null)
 * @method self   string(string $column, ?int $length = null)
 * @method self   text(string $column)
 * @method self   mediumText(string $column)
 * @method self   longText(string $column)
 * @method self   integer(string $column, bool $autoIncrement = false, bool $unsigned = false)
 * @method self   tinyInteger(string $column, bool $autoIncrement = false, bool $unsigned = false)
 * @method self   smallInteger(string $column, bool $autoIncrement = false, bool $unsigned = false)
 * @method self   mediumInteger(string $column, bool $autoIncrement = false, bool $unsigned = false)
 * @method self   bigInteger(string $column, bool $autoIncrement = false, bool $unsigned = false)
 * @method self   unsignedInteger(string $column, bool $autoIncrement = false)
 * @method self   unsignedTinyInteger(string $column, bool $autoIncrement = false)
 * @method self   unsignedSmallInteger(string $column, bool $autoIncrement = false)
 * @method self   unsignedMediumInteger(string $column, bool $autoIncrement = false)
 * @method self   unsignedBigInteger(string $column, bool $autoIncrement = false)
 * @method self   float(string $column, int $total = 8, int $places = 2)
 * @method self   double(string $column, ?int $total = null, ?int $places = null)
 * @method self   decimal(string $column, int $total = 8, int $places = 2)
 * @method self   unsignedDecimal(string $column, int $total = 8, int $places = 2)
 * @method self   boolean(string $column)
 * @method self   enum(string $column, array $allowed)
 * @method self   json(string $column)
 * @method self   jsonb(string $column)
 * @method self   date(string $column)
 * @method self   dateTime(string $column, int $precision = 0)
 * @method self   time(string $column, int $precision = 0)
 * @method self   timestamp(string $column, int $precision = 0)
 * @method self   timestamps(int $precision = 0)
 * @method self   softDeletes(string $column = 'deleted', int $precision = 0)
 * @method self   year(string $column)
 * @method self   binary(string $column)
 * @method self   uuid(string $column)
 * @method self   ipAddress(string $column)
 * @method self   macAddress(string $column)
 * @method self   geometry(string $column)
 * @method self   point(string $column, ?int $srid = null)
 * @method self   lineString(string $column)
 * @method self   polygon(string $column)
 * @method self   geometryCollection(string $column)
 * @method self   multiPoint(string $column)
 * @method self   multiLineString(string $column)
 * @method self   multiPolygon(string $column)
 * @method self   addColumn(string $type, string $name, array $parameters = [])
 * @method self   removeColumn(string $name)
 * @method string getTable()
 * @method array  getColumns()
 * @method array  getCommands()
 * @method array  getAddedColumns()
 * @method array  getChangedColumns()
 */
class Blueprint
{
    use Macroable;

    /**
     * The storage engine that should be used for the table.
     *
     * @var string
     */
    public $engine;

    /**
     * The default character set that should be used for the table.
     */
    public $charset;

    /**
     * The collation that should be used for the table.
     */
    public $collation;

    /**
     * Whether to make the table temporary.
     *
     * @var bool
     */
    public $temporary = false;

    /**
     * The table the blueprint describes.
     *
     * @var string
     */
    protected $table;

    /**
     * The columns that should be added to the table.
     *
     * @var \Illuminate\Support\Fluent[]
     */
    protected $columns = [];

    /**
     * The commands that should be run for the table.
     *
     * @var \Illuminate\Support\Fluent[]
     */
    protected $commands = [];

    /**
     * Create a new schema blueprint.
     *
     * @param string $table
     */
    public function __construct($table, Closure $callback = null)
    {
        $this->table = $table;

        if (!is_null($callback)) {
            $callback($this);
        }
    }

    public function build(DbConnectionInterface $connection, Grammar $grammar): void
    {
        foreach ($this->toSql($connection, $grammar) as $statement) {
            $connection->query($statement);
        }
    }

    public function toSql(DbConnectionInterface $connection, Grammar $grammar): array
    {
        $this->addImpliedCommands($grammar);

        $statements = [];
        foreach ($this->commands as $command) {
            $method = 'compile'.ucfirst($command->name);

            if (method_exists($grammar, $method)) {
                if (!is_null($sql = $grammar->{$method}($this, $command, $connection))) {
                    $statements = array_merge($statements, (array) $sql);
                }
            }
        }

        return $statements;
    }

    public function addFluentCommands(Grammar $grammar): void
    {
        foreach ($this->columns as $column) {
            foreach ($grammar->getFluentCommands() as $commandName) {
                $attributeName = lcfirst($commandName);

                if (!isset($column->{$attributeName})) {
                    continue;
                }

                $value = $column->{$attributeName};

                $this->addCommand(
                    $commandName,
                    compact('value', 'column')
                );
            }
        }
    }

    public function create(): Fluent
    {
        return $this->addCommand('create');
    }

    public function modify(): Fluent
    {
        return $this->addCommand('modify');
    }

    public function temporary(): void
    {
        $this->temporary = true;
    }

    public function drop(): Fluent
    {
        return $this->addCommand('drop');
    }

    public function dropIfExists(): Fluent
    {
        return $this->addCommand('dropIfExists');
    }

    public function dropColumn($columns): Fluent
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        return $this->addCommand('dropColumn', compact('columns'));
    }

    public function renameColumn(string $from, string $to): Fluent
    {
        return $this->addCommand('renameColumn', compact('from', 'to'));
    }

    public function dropPrimary($index = null): Fluent
    {
        return $this->dropIndexCommand('dropPrimary', 'primary', $index);
    }

    public function dropUnique($index): Fluent
    {
        return $this->dropIndexCommand('dropUnique', 'unique', $index);
    }

    public function dropIndex($index): Fluent
    {
        return $this->dropIndexCommand('dropIndex', 'index', $index);
    }

    public function dropSpatialIndex($index): Fluent
    {
        return $this->dropIndexCommand('dropSpatialIndex', 'spatialIndex', $index);
    }

    public function dropForeign($index): Fluent
    {
        return $this->dropIndexCommand('dropForeign', 'foreign', $index);
    }

    public function renameIndex(string $from, string $to): Fluent
    {
        return $this->addCommand('renameIndex', compact('from', 'to'));
    }

    public function dropTimestamps(): void
    {
        $this->dropColumn('created', 'updated');
    }

    public function dropSoftDeletes(): void
    {
        $this->dropColumn('deleted');
    }

    public function rename(string $to): Fluent
    {
        return $this->addCommand('rename', compact('to'));
    }

    public function primary($columns, ?string $name = null, ?string $algorithm = null): Fluent
    {
        return $this->indexCommand('primary', $columns, $name, $algorithm);
    }

    public function unique($columns, ?string $name = null, ?string $algorithm = null): Fluent
    {
        return $this->indexCommand('unique', $columns, $name, $algorithm);
    }

    public function index($columns, ?string $name = null, ?string $algorithm = null): Fluent
    {
        return $this->indexCommand('index', $columns, $name, $algorithm);
    }

    public function spatialIndex($columns, ?string $name = null): Fluent
    {
        return $this->indexCommand('spatialIndex', $columns, $name);
    }

    public function fullTextIndex($columns, ?string $name = null): Fluent
    {
        return $this->indexCommand('fullTextIndex', $columns, $name);
    }

    public function foreign($columns, ?string $name = null): Fluent
    {
        return $this->indexCommand('foreign', $columns, $name);
    }

    public function increments(string $column): Fluent
    {
        return $this->unsignedInteger($column, true);
    }

    public function tinyIncrements(string $column): Fluent
    {
        return $this->unsignedTinyInteger($column, true);
    }

    public function smallIncrements(string $column): Fluent
    {
        return $this->unsignedSmallInteger($column, true);
    }

    public function mediumIncrements(string $column): Fluent
    {
        return $this->unsignedMediumInteger($column, true);
    }

    public function bigIncrements(string $column): Fluent
    {
        return $this->unsignedBigInteger($column, true);
    }

    public function char(string $column, ?int $length = null): Fluent
    {
        $length = $length ?: Builder::$defaultStringLength;

        return $this->addColumn('char', $column, compact('length'));
    }

    public function string(string $column, ?int $length = null): Fluent
    {
        $length = $length ?: Builder::$defaultStringLength;

        return $this->addColumn('string', $column, compact('length'));
    }

    public function text(string $column): Fluent
    {
        return $this->addColumn('text', $column);
    }

    public function mediumText(string $column): Fluent
    {
        return $this->addColumn('mediumText', $column);
    }

    public function longText(string $column): Fluent
    {
        return $this->addColumn('longText', $column);
    }

    public function integer(string $column, bool $autoIncrement = false, bool $unsigned = false): Fluent
    {
        return $this->addColumn('integer', $column, compact('autoIncrement', 'unsigned'));
    }

    public function tinyInteger(string $column, bool $autoIncrement = false, bool $unsigned = false): Fluent
    {
        return $this->addColumn('tinyInteger', $column, compact('autoIncrement', 'unsigned'));
    }

    public function smallInteger(string $column, bool $autoIncrement = false, bool $unsigned = false): Fluent
    {
        return $this->addColumn('smallInteger', $column, compact('autoIncrement', 'unsigned'));
    }

    public function mediumInteger(string $column, bool $autoIncrement = false, bool $unsigned = false): Fluent
    {
        return $this->addColumn('mediumInteger', $column, compact('autoIncrement', 'unsigned'));
    }

    public function bigInteger(string $column, bool $autoIncrement = false, bool $unsigned = false): Fluent
    {
        return $this->addColumn('bigInteger', $column, compact('autoIncrement', 'unsigned'));
    }

    public function unsignedInteger(string $column, bool $autoIncrement = false): Fluent
    {
        return $this->integer($column, $autoIncrement, true);
    }

    public function unsignedTinyInteger(string $column, bool $autoIncrement = false): Fluent
    {
        return $this->tinyInteger($column, $autoIncrement, true);
    }

    public function unsignedSmallInteger(string $column, bool $autoIncrement = false): Fluent
    {
        return $this->smallInteger($column, $autoIncrement, true);
    }

    public function unsignedMediumInteger(string $column, bool $autoIncrement = false): Fluent
    {
        return $this->mediumInteger($column, $autoIncrement, true);
    }

    public function unsignedBigInteger(string $column, bool $autoIncrement = false): Fluent
    {
        return $this->bigInteger($column, $autoIncrement, true);
    }

    public function float(string $column, int $total = 8, int $places = 2): Fluent
    {
        return $this->addColumn('float', $column, compact('total', 'places'));
    }

    public function double(string $column, ?int $total = null, ?int $places = null): Fluent
    {
        return $this->addColumn('double', $column, compact('total', 'places'));
    }

    public function decimal(string $column, int $total = 8, int $places = 2): Fluent
    {
        return $this->addColumn('decimal', $column, compact('total', 'places'));
    }

    public function unsignedDecimal(string $column, int $total = 8, int $places = 2): Fluent
    {
        return $this->addColumn('decimal', $column, [
            'total' => $total, 'places' => $places, 'unsigned' => true,
        ]);
    }

    public function boolean(string $column): Fluent
    {
        return $this->addColumn('boolean', $column);
    }

    public function enum(string $column, array $allowed): Fluent
    {
        return $this->addColumn('enum', $column, compact('allowed'));
    }

    public function json(string $column): Fluent
    {
        return $this->addColumn('json', $column);
    }

    public function jsonb(string $column): Fluent
    {
        return $this->addColumn('jsonb', $column);
    }

    public function date(string $column): Fluent
    {
        return $this->addColumn('date', $column);
    }

    public function dateTime(string $column, int $precision = 0): Fluent
    {
        return $this->addColumn('dateTime', $column, compact('precision'));
    }

    public function time(string $column, int $precision = 0): Fluent
    {
        return $this->addColumn('time', $column, compact('precision'));
    }

    public function timestamp(string $column, int $precision = 0): Fluent
    {
        return $this->addColumn('timestamp', $column, compact('precision'));
    }

    public function timestamps(int $precision = 0): void
    {
        $this->bigInteger('created')->nullable();
        $this->bigInteger('updated')->nullable();
    }

    public function softDeletes(string $column = 'deleted', int $precision = 0): Fluent
    {
        return $this->timestamp($column, $precision)->nullable();
    }

    public function year(string $column): Fluent
    {
        return $this->addColumn('year', $column);
    }

    public function binary(string $column): Fluent
    {
        return $this->addColumn('binary', $column);
    }

    public function uuid(string $column): Fluent
    {
        return $this->addColumn('uuid', $column);
    }

    public function ipAddress(string $column): Fluent
    {
        return $this->addColumn('ipAddress', $column);
    }

    public function macAddress(string $column): Fluent
    {
        return $this->addColumn('macAddress', $column);
    }

    public function geometry(string $column): Fluent
    {
        return $this->addColumn('geometry', $column);
    }

    public function point(string $column, ?int $srid = null): Fluent
    {
        return $this->addColumn('point', $column, compact('srid'));
    }

    public function lineString(string $column): Fluent
    {
        return $this->addColumn('linestring', $column);
    }

    public function polygon(string $column): Fluent
    {
        return $this->addColumn('polygon', $column);
    }

    public function geometryCollection(string $column): Fluent
    {
        return $this->addColumn('geometrycollection', $column);
    }

    public function multiPoint(string $column): Fluent
    {
        return $this->addColumn('multipoint', $column);
    }

    public function multiLineString(string $column): Fluent
    {
        return $this->addColumn('multilinestring', $column);
    }

    public function multiPolygon(string $column): Fluent
    {
        return $this->addColumn('multipolygon', $column);
    }

    public function addColumn(string $type, string $name, array $parameters = []): Fluent
    {
        $this->columns[] = $column = new Fluent(
            array_merge(compact('type', 'name'), $parameters)
        );

        return $column;
    }

    public function removeColumn(string $name)
    {
        $this->columns = array_values(array_filter($this->columns, function ($c) use ($name) {
            return $c['attributes']['name'] != $name;
        }));

        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    public function getAddedColumns(): array
    {
        return array_filter($this->columns, function ($column) {
            return !$column->change;
        });
    }

    public function getChangedColumns(): array
    {
        return array_filter($this->columns, function ($column) {
            return (bool) $column->change;
        });
    }

    protected function commandsNamed(array $names)
    {
        return collect($this->commands)->filter(function ($command) use ($names) {
            return in_array($command->name, $names);
        });
    }

    protected function addImpliedCommands(Grammar $grammar): void
    {
        if (count($this->getAddedColumns()) > 0 && !$this->creating()) {
            array_unshift($this->commands, $this->createCommand('add'));
        }

        if (count($this->getChangedColumns()) > 0 && !$this->creating()) {
            array_unshift($this->commands, $this->createCommand('change'));
        }

        $this->addFluentIndexes();
        $this->addFluentCommands($grammar);
    }

    /**
     * Add the index commands fluently specified on columns.
     */
    protected function addFluentIndexes(): void
    {
        foreach ($this->columns as $column) {
            foreach (['primary', 'unique', 'index', 'spatialIndex'] as $index) {
                if (true === $column->{$index}) {
                    $this->{$index}($column->name);

                    continue 2;
                }
                if (isset($column->{$index})) {
                    $this->{$index}($column->name, $column->{$index});

                    continue 2;
                }
            }
        }
    }

    protected function creating()
    {
        return collect($this->commands)->contains(function ($command) {
            return 'create' == $command->name;
        });
    }

    protected function indexCommand(string $type, $columns, ?string $index, ?string $algorithm = null): Fluent
    {
        $columns = (array) $columns;
        $index = $index ?: $this->createIndexName($type, $columns);

        return $this->addCommand(
            $type,
            compact('index', 'columns', 'algorithm')
        );
    }

    protected function dropIndexCommand(string $command, string $type, ?string $index): Fluent
    {
        $columns = [];
        if (is_array($index)) {
            $index = $this->createIndexName($type, $columns = $index);
        }

        return $this->indexCommand($command, $columns, $index);
    }

    protected function createIndexName(string $type, array $columns): string
    {
        $index = strtolower($this->table.'_'.implode('_', $columns).'_'.$type);

        return str_replace(['-', '.'], '_', $index);
    }

    protected function addCommand(string $name, array $parameters = []): Fluent
    {
        $this->commands[] = $command = $this->createCommand($name, $parameters);

        return $command;
    }

    protected function createCommand(string $name, array $parameters = []): Fluent
    {
        return new Fluent(array_merge(compact('name'), $parameters));
    }
}
