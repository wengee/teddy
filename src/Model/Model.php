<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 15:23:27 +0800
 */

namespace Teddy\Model;

use ArrayAccess;
use Exception;
use Illuminate\Support\Str;
use JsonSerializable;
use Teddy\Database\DbConnectionInterface;
use Teddy\Database\DbException;
use Teddy\Database\QueryBuilder;
use Teddy\Database\RawSQL;
use Teddy\Interfaces\ArrayableInterface;
use Teddy\Interfaces\JsonableInterface;

abstract class Model implements ArrayAccess, JsonSerializable
{
    protected $items = [];

    protected $isNewRecord = true;

    protected $metaInfo;

    protected $connection = null;

    public function offsetExists($offset): bool
    {
        return is_string($offset) && $this->hasAttribute($offset);
    }

    public function offsetGet($offset)
    {
        if (is_string($offset)) {
            return $this->getAttribute($offset);
        }

        return null;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_string($offset)) {
            $this->setAttribute($offset, $value);
        }
    }

    public function offsetUnset($offset): void
    {
        if (is_string($offset) && $this->hasAttribute($offset)) {
            $this->setAttribute($offset, null);
        }
    }

    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof JsonableInterface) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof ArrayableInterface) {
                return $value->toArray();
            }

            return $value;
        }, array_filter($this->items, function ($key) {
            return !is_string($key) || $key{0} !== '_';
        }, ARRAY_FILTER_USE_KEY));
    }

    public function isNewRecord(): bool
    {
        return $this->isNewRecord;
    }

    public function setConnection($connection): self
    {
        if ($connection instanceof DbConnectionInterface) {
            $this->connection = $connection;
        } elseif (is_string($connection)) {
            $this->connection = db($connection);
        }

        return $this;
    }

    public function assign(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function save(bool $quiet = true)
    {
        try {
            $this->doSave();
        } catch (Exception $e) {
            if ($quiet) {
                return false;
            } else {
                throw $e;
            }
        }

        return true;
    }

    public function delete(bool $quiet = true)
    {
        try {
            $this->doDelete();
        } catch (Exception $e) {
            if ($quiet) {
                return false;
            } else {
                throw $e;
            }
        }

        return true;
    }

    public static function query(?DbConnectionInterface $db = null): QueryBuilder
    {
        if ($db === null) {
            $connectionName = app('modelManager')->metaInfo(static::class)->connectionName();
            return new QueryBuilder(db($connectionName), static::class);
        }

        return new QueryBuilder($db, static::class);
    }

    public static function raw(string $sql): RawSQL
    {
        return new RawSQL($sql);
    }

    public static function fetch(array $conditions)
    {
        $query = static::query();
        foreach ($conditions as $key => $value) {
            $query = $query->where($key, $value);
        }

        return $query->first();
    }

    protected function hasAttribute(string $key)
    {
        if ($this->hasGetMutator($key) || $this->hasSetMutator($key) || $this->hasColumn($key)) {
            return true;
        }

        return false;
    }

    protected function setAttribute(string $key, $value): void
    {
        if ($this->hasSetMutator($key)) {
            $this->setMutatedAttributeValue($key, $value);
        } elseif ($this->hasColumn($key)) {
            if ($key === null) {
                $this->items[] = $value;
            } else {
                $this->items[$key] = $value;
            }
        }
    }

    protected function getAttribute(string $key, $default = null)
    {
        if ($this->hasGetMutator($key)) {
            return $this->getMutatedAttributeValue($key);
        } elseif ($this->hasColumn($key)) {
            if (array_key_exists($key, $this->items)) {
                return $this->items[$key];
            }
        }

        return $default;
    }

    protected function hasSetMutator(string $key): bool
    {
        return method_exists($this, 'set' . Str::studly($key) . 'Attribute');
    }

    protected function setMutatedAttributeValue(string $key, $value): void
    {
        $this->{'set' . Str::studly($key) . 'Attribute'}($value);
    }

    protected function hasGetMutator(string $key): bool
    {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute');
    }

    protected function getMutatedAttributeValue(string $key)
    {
        return $this->{'get' . Str::studly($key) . 'Attribute'}();
    }

    protected function hasColumn(string $key): bool
    {
        if (!method_exists($this, 'metaInfo')) {
            return true;
        }

        return $this->metaInfo()->hasColumn($key);
    }

    protected function trigger(string $action, ...$args): void
    {
        if (method_exists($this, $action)) {
            $this->{$action}(...$args);
        }
    }

    protected function metaInfo()
    {
        if (!$this->metaInfo) {
            $this->metaInfo = app('modelManager')->metaInfo($this);
        }

        return $this->metaInfo;
    }

    protected function getDbAttributes(): array
    {
        $columns = $this->metaInfo()->getColumns();
        if (empty($columns)) {
            return [];
        }

        $attributes = $this->items;
        foreach ($columns as $key => $column) {
            $value = isset($attributes[$key]) ? $attributes[$key] : $column->defaultValue();
            $attributes[$key] = $column->dbValue($value);
        }

        return $attributes;
    }

    protected function setDbAttributes(array $data)
    {
        $this->items = [];
        $this->isNewRecord = false;

        $metaInfo = $this->metaInfo();
        $columns = $metaInfo->getColumns();
        foreach ($data as $key => $value) {
            $key = $metaInfo->transformKey($key, false);
            if (isset($columns[$key])) {
                $this->items[$key] = $columns[$key]->value($value);
            }
        }
    }

    protected function doSave()
    {
        $metaInfo = $this->metaInfo();
        $primaryKeys = $metaInfo->primaryKeys();
        if (empty($primaryKeys)) {
            throw new DbException('Primary keys is not defined.');
        }

        $this->trigger('beforeSave');

        $query = $this->buildQuery();
        $attributes = $this->getDbAttributes();
        if ($this->isNewRecord()) {
            $this->trigger('beforeInsert');
            $id = $query->insert($attributes, true);

            $autoIncrement = $metaInfo->autoIncrement();
            if ($autoIncrement && $id > 0) {
                $this->setAttribute($autoIncrement, (int) $id);
            }

            $this->trigger('afterInsert');
            $this->isNewRecord = false;
        } else {
            $this->trigger('beforeUpdate');
            foreach (array_only($attributes, $primaryKeys) as $key => $value) {
                $query->where($key, $value);
            }

            $data = array_except($attributes, $primaryKeys);
            $query->limit(1)->update((array) $data);
            $this->trigger('afterUpdate');
        }

        $this->trigger('afterSave');
    }

    protected function doDelete()
    {
        $metaInfo = $this->metaInfo();
        $primaryKeys = $metaInfo->primaryKeys();
        if (empty($primaryKeys)) {
            throw new DbException('Primary keys is not defined.');
        }

        $this->trigger('beforeDelete');

        $query = $this->buildQuery();
        $attributes = $this->getDbAttributes();
        if (!$this->isNewRecord()) {
            foreach (array_only($attributes, $primaryKeys) as $key => $value) {
                $query->where($key, $value);
            }

            $query->limit(1)->delete();
        }

        $this->trigger('afterDelete');
    }

    protected function buildQuery(): QueryBuilder
    {
        if ($this->connection) {
            return static::query($this->connection);
        } else {
            $connectionName = $this->metaInfo()->connectionName();
            return static::query(db($connectionName));
        }
    }
}