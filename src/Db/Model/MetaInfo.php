<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-06 17:08:36 +0800
 */
namespace SlimExtra\Db\Model;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use Illuminate\Support\Str;
use ReflectionClass;
use SlimExtra\Db\Model;
use SlimExtra\Db\Model\Columns\ColumnInterface;
use SlimExtra\Exception;

class MetaInfo
{
    private $className;

    private $tableName;

    private $primaryKeys = [];

    private $autoIncrement;

    private $columnMap = [];

    private $dbColumnMap = [];

    private $columns = [];

    private $setDbPropertyMethod;

    private $getDbPropertyMethod;

    public function __construct($model, ?Reader $reader = null)
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new Exception('Invalid parameters.');
        }

        if (is_object($model)) {
            $className = get_class($model);
        } else {
            $className = (string) $model;
        }

        $this->className = $className;
        $reader = $reader ?: new AnnotationReader;
        $reflection = new ReflectionClass($className);
        $this->setDbPropertyMethod = $reflection->getMethod('setDbAttributes');
        $this->getDbPropertyMethod = $reflection->getMethod('getDbAttributes');

        $annotations = $reader->getClassAnnotations($reflection);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Table) {
                $this->tableName = $annotation->name;
            } elseif ($annotation instanceof ColumnInterface) {
                $propertyName = $annotation->getName();

                $field = $annotation->getField();
                if ($field) {
                    $this->columnMap[$propertyName] = $field;
                }

                if ($annotation->isPrimaryKey()) {
                    $this->primaryKeys[] = $propertyName;
                }

                if ($annotation->isAutoIncrement()) {
                    $this->autoIncrement = $propertyName;
                }

                $this->columns[$propertyName] = $annotation;
            }
        }

        $this->dbColumnMap = array_flip($this->columnMap);
        if (empty($this->tableName)) {
            $this->tableName = Str::snake($reflection->getShortName());
        }
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

    public function transformKey(string $key, bool $toDb = true)
    {
        if ($toDb) {
            return isset($this->columnMap[$key]) ? $this->columnMap[$key] : $key;
        } else {
            return isset($this->dbColumnMap[$key]) ? $this->dbColumnMap[$key] : $key;
        }
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

    public function makeInstance(array $data)
    {
        $object = new $this->className;
        $closure = $this->setDbPropertyMethod->getClosure($object);
        call_user_func($closure, $data);
        return $object;
    }
}
