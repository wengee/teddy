<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-03-07 22:50:19 +0800
 */

namespace Teddy\Model;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Illuminate\Support\Str;
use ReflectionClass;
use Teddy\Exception;
use Teddy\Model\Columns\ColumnInterface;

class MetaInfo
{
    private $className;

    private $connectionName;

    private $tableName;

    private $primaryKeys = [];

    private $autoIncrement;

    private $columnMap = [];

    private $dbColumnMap = [];

    /**
     * @property ColumnInterface[]
     */
    private $columns = [];

    private $setDbPropertyMethod;

    private $getDbPropertyMethod;

    public function __construct($model, ?Reader $reader = null)
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new Exception(sprintf('Invalid parameters [%s].', $model));
        }

        if (is_object($model)) {
            $className = get_class($model);
        } else {
            $className = (string) $model;
        }

        $this->className           = $className;
        $reader                    = $reader ?: new AnnotationReader();
        $reflection                = new ReflectionClass($className);
        $this->setDbPropertyMethod = $reflection->getMethod('setDbAttributes');
        $this->getDbPropertyMethod = $reflection->getMethod('getDbAttributes');

        $annotations = $reader->getClassAnnotations($reflection);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Table) {
                $this->tableName = $annotation->name;
            } elseif ($annotation instanceof Connection) {
                $this->connectionName = $annotation->name;
            } elseif ($annotation instanceof ColumnInterface) {
                $propertyName = $annotation->getName();
                $field        = $annotation->getField() ?: $propertyName;

                $this->columnMap[$propertyName] = $field;
                $this->dbColumnMap[$field]      = $propertyName;

                if ($annotation->isPrimaryKey()) {
                    $this->primaryKeys[] = $propertyName;
                }

                if ($annotation->isAutoIncrement()) {
                    $this->autoIncrement = $propertyName;
                }

                $this->columns[$propertyName] = $annotation;
            }
        }

        if (empty($this->tableName)) {
            $this->tableName = Str::snake($reflection->getShortName());
        }
    }

    public function connectionName(): string
    {
        return $this->connectionName ?: 'default';
    }

    public function tableName(): string
    {
        return $this->tableName;
    }

    public function primaryKeys(): array
    {
        return $this->primaryKeys;
    }

    public function autoIncrement()
    {
        return $this->autoIncrement;
    }

    public function convertToPhpColumn(string $key): string
    {
        return $this->dbColumnMap[$key] ?? $key;
    }

    public function convertToDbColumn(string $key): string
    {
        return $this->columnMap[$key] ?? $key;
    }

    public function transformKey(string $key, bool $toDb = true)
    {
        if ($toDb) {
            return $this->columnMap[$key] ?? $key;
        }

        return $this->dbColumnMap[$key] ?? $key;
    }

    public function getValue($key, $value, bool $toDb = false)
    {
        if (empty($this->columns) || empty($this->columns[$key])) {
            return $value;
        }

        $column = $this->columns[$key];

        return $toDb ? $column->dbValue($value) : $column->value($value);
    }

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
            /** @var ColumnInterface $column */
            return $column->defaultValue();
        }, $this->columns);
    }

    public function hasColumn(string $columnName): bool
    {
        return isset($this->columns[$columnName]);
    }

    public function makeInstance(array $data)
    {
        $clsName = $this->className;
        $object  = new $clsName(false);
        $closure = $this->setDbPropertyMethod->getClosure($object);
        call_user_func($closure, $data);

        return $object;
    }
}
