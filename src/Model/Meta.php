<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-18 17:31:31 +0800
 */

namespace Teddy\Model;

use Illuminate\Support\Str;
use ReflectionClass;
use Teddy\Exception;
use Teddy\Model\Columns\ColumnInterface;

class Meta
{
    /**
     * @var string
     */
    private $className = '';

    /**
     * @var string
     */
    private $connectionName = '';

    /**
     * @var string
     */
    private $tableName = '';

    /**
     * @var string
     */
    private $suffixedTableName = '';

    /**
     * @var array
     */
    private $primaryKeys = [];

    /**
     * @var string
     */
    private $autoIncrementKey = '';

    /**
     * @var array
     */
    private $columnMap = [];

    /**
     * @var array
     */
    private $dbColumnMap = [];

    /**
     * @var ColumnInterface[]
     */
    private $columns = [];

    public function __construct(Model|string $model)
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new Exception(sprintf('Invalid parameters [%s].', $model));
        }

        if (is_object($model)) {
            $className = get_class($model);
        } else {
            $className = (string) $model;
        }
        $this->className = $className;

        $ref   = new ReflectionClass($className);
        $attrs = $ref->getAttributes();
        foreach ($attrs as $attr) {
            $annotation = $attr->newInstance();

            if ($annotation instanceof Table) {
                $this->tableName         = $annotation->getName();
                $this->suffixedTableName = $annotation->getSuffixed();
            } elseif ($annotation instanceof Connection) {
                $this->connectionName = $annotation->getName();
            } elseif ($annotation instanceof ColumnInterface) {
                $propertyName = $annotation->getName();
                $field        = $annotation->getField() ?: $propertyName;

                $this->columnMap[$propertyName] = $field;
                $this->dbColumnMap[$field]      = $propertyName;

                if ($annotation->isPrimaryKey()) {
                    $this->primaryKeys[] = $propertyName;
                }

                if ($annotation->isAutoIncrement()) {
                    $this->autoIncrementKey = $propertyName;
                }

                $this->columns[$propertyName] = $annotation;
            }
        }

        if (!$this->suffixedTableName && !$this->tableName) {
            $this->tableName = Str::snake($ref->getShortName());
        }
    }

    public function getConnectionName(): string
    {
        return $this->connectionName ?: 'default';
    }

    public function getTableName(?string $tableSuffix = null): string
    {
        if (!$this->suffixedTableName || !$tableSuffix) {
            if (!$this->tableName) {
                throw new Exception('Table suffix is required.');
            }

            return $this->tableName;
        }

        return strtr($this->suffixedTableName, [
            '{SUFFIX}' => $tableSuffix,
        ]);
    }

    public function getPrimaryKeys(): array
    {
        return $this->primaryKeys;
    }

    public function getAutoIncrementKey()
    {
        return $this->autoIncrementKey;
    }

    public function convertToPhpColumn(string $key): string
    {
        return $this->dbColumnMap[$key] ?? $key;
    }

    public function convertToDbColumn(string $key): string
    {
        return $this->columnMap[$key] ?? $key;
    }

    public function convertToPhpValue(string $key, $value)
    {
        if (empty($this->columns) || empty($this->columns[$key])) {
            return $value;
        }

        return $this->columns[$key]->convertToPhpValue($value);
    }

    public function convertToDbValue(string $key, $value)
    {
        if (empty($this->columns) || empty($this->columns[$key])) {
            return $value;
        }

        return $this->columns[$key]->convertToDbValue($value);
    }

    public function getValue($key, $value, bool $toDb = false)
    {
        if (empty($this->columns) || empty($this->columns[$key])) {
            return $value;
        }

        $column = $this->columns[$key];

        return $toDb ? $column->convertToDbValue($value) : $column->convertToPhpValue($value);
    }

    /**
     * @return ColumnInterface[]
     */
    public function getColumns(): array
    {
        return (array) $this->columns;
    }

    public function getDefaults(): array
    {
        if (!$this->columns) {
            return [];
        }

        return array_map(function ($column) {
            /**
             * @var ColumnInterface $column
             */
            return $column->defaultValue();
        }, $this->columns);
    }

    public function hasColumn(string $columnName): bool
    {
        return isset($this->columns[$columnName]);
    }

    public function makeInstance(array $data = [], ?string $tableSuffix = '')
    {
        return call_user_func($this->className.'::createFromDb', $data, $tableSuffix);
    }
}
