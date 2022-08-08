<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:43:17 +0800
 */

namespace Teddy\Model;

use ArrayAccess;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use JsonSerializable;
use Teddy\Database\DatabaseInterface;
use Teddy\Database\DbException;
use Teddy\Database\QueryBuilder;
use Teddy\Database\RawSQL;
use Teddy\Interfaces\ArrayableInterface;
use Teddy\Interfaces\JsonableInterface;

abstract class Model implements ArrayAccess, JsonSerializable
{
    use Macroable;

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $hidden = [];

    /**
     * @var bool
     */
    protected $isNewRecord = true;

    /**
     * @var bool
     */
    protected $isModified = false;

    /**
     * @var null|DatabaseInterface|string
     */
    protected $connection;

    /**
     * @var Meta
     */
    protected $meta;

    final public function __construct()
    {
        $this->meta  = app('modelManager')->getMeta(static::class);
        $this->items = $this->meta->getDefaults();
    }

    public function __serialize(): array
    {
        return [
            'items'       => $this->items,
            'isNewRecord' => $this->isNewRecord,
            'isModified'  => $this->isModified,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->items       = $data['items'] ?? [];
        $this->isNewRecord = $data['isNewRecord'] ?? false;
        $this->isModified  = $data['isModified'] ?? false;
    }

    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && $this->hasAttribute($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (is_string($offset)) {
            return $this->getAttribute($offset);
        }

        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_string($offset)) {
            $this->setAttribute($offset, $value);
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (is_string($offset) && $this->hasAttribute($offset)) {
            $this->setAttribute($offset, null);
        }
    }

    public function jsonSerialize(): object
    {
        return (object) $this->toArray($this->hidden, true);
    }

    public function toArray(array $keys = [], bool $except = false): array
    {
        $values = array_map(function ($value) {
            if ($value instanceof ArrayableInterface) {
                return $value->toArray();
            }
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            }
            if ($value instanceof JsonableInterface) {
                return json_decode($value->toJson(), true);
            }

            return $value;
        }, array_filter($this->items, function ($key) {
            return !is_string($key) || '_' !== $key[0];
        }, ARRAY_FILTER_USE_KEY));

        if ($keys) {
            $values = $except ? Arr::except($values, $keys) : Arr::only($values, $keys);
        }

        return $values;
    }

    public function isNewRecord(): bool
    {
        return $this->isNewRecord;
    }

    public function isModified(): bool
    {
        return $this->isModified;
    }

    public function setConnection($connection): self
    {
        if ($connection instanceof DatabaseInterface) {
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

    public function save(bool $quiet = true): bool
    {
        try {
            $this->doSave();
        } catch (Exception $e) {
            log_exception($e);
            if ($quiet) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    public function delete(bool $quiet = true): bool
    {
        try {
            $this->doDelete();
        } catch (Exception $e) {
            log_exception($e);
            if ($quiet) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    public static function query(?DatabaseInterface $db = null): QueryBuilder
    {
        if (null === $db) {
            $connectionName = app('modelManager')->getMeta(static::class)->connectionName();

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
            $this->isModified = true;
            $this->setMutatedAttributeValue($key, $value);
        } elseif ($this->hasColumn($key)) {
            $this->isModified  = $this->isModified || !array_key_exists($key, $this->items) || ($this->items[$key] !== $value);
            $this->items[$key] = $value;
        }
    }

    protected function getAttribute(string $key, $default = null)
    {
        if ($this->hasGetMutator($key)) {
            return $this->getMutatedAttributeValue($key);
        }
        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return $default;
    }

    protected function hasSetMutator(string $key): bool
    {
        return method_exists($this, 'set'.Str::studly($key).'Attribute');
    }

    protected function setMutatedAttributeValue(string $key, $value): void
    {
        $this->{'set'.Str::studly($key).'Attribute'}($value);
    }

    protected function hasGetMutator(string $key): bool
    {
        return method_exists($this, 'get'.Str::studly($key).'Attribute');
    }

    protected function getMutatedAttributeValue(string $key)
    {
        return $this->{'get'.Str::studly($key).'Attribute'}();
    }

    protected function hasColumn(string $key): bool
    {
        return $this->meta->hasColumn($key);
    }

    protected function getDbAttributes(): array
    {
        $columns = $this->meta->getColumns();
        if (empty($columns)) {
            return [];
        }

        $attributes = $this->items;
        foreach ($columns as $key => $column) {
            $value = array_key_exists($key, $attributes) ? $attributes[$key] : $column->defaultValue();

            $attributes[$key] = $column->convertToDbValue($value);
        }

        return $attributes;
    }

    protected function setDbAttributes(array $data): void
    {
        $this->items       = [];
        $this->isNewRecord = false;

        $columns = $this->meta->getColumns();
        foreach ($data as $key => $value) {
            $key = $this->meta->convertToPhpColumn($key);
            if (isset($columns[$key])) {
                $this->items[$key] = $columns[$key]->convertToPhpValue($value);
            } else {
                $this->items[$key] = $value;
            }
        }
    }

    protected function triggerEvent(string $action, ...$args): void
    {
        if (method_exists($this, $action)) {
            $this->{$action}(...$args);
        }
    }

    protected function doSave(): void
    {
        if (!$this->isNewRecord() && !$this->isModified()) {
            return;
        }

        $primaryKeys = $this->meta->primaryKeys();
        if (empty($primaryKeys)) {
            throw new DbException('Primary keys is not defined.');
        }

        $this->triggerEvent('beforeSave');

        $query      = $this->buildQuery();
        $attributes = $this->getDbAttributes();
        if ($this->isNewRecord()) {
            $this->triggerEvent('beforeInsert');
            $id = $query->insert($attributes, true);

            $autoIncrement = $this->meta->autoIncrement();
            if ($autoIncrement && $id > 0) {
                $this->setAttribute($autoIncrement, (int) $id);
            }

            $this->triggerEvent('afterInsert');
            $this->isNewRecord = false;
        } else {
            $this->triggerEvent('beforeUpdate');
            foreach (Arr::only($attributes, $primaryKeys) as $key => $value) {
                $query->where($key, $value);
            }

            $data = Arr::except($attributes, $primaryKeys);
            $query->limit(1)->update((array) $data);
            $this->triggerEvent('afterUpdate');
        }

        $this->isModified = false;
        $this->triggerEvent('afterSave');
    }

    protected function doDelete(): void
    {
        $primaryKeys = $this->meta->primaryKeys();
        if (empty($primaryKeys)) {
            throw new DbException('Primary keys is not defined.');
        }

        $this->triggerEvent('beforeDelete');

        $query      = $this->buildQuery();
        $attributes = $this->getDbAttributes();
        if (!$this->isNewRecord()) {
            foreach (Arr::only($attributes, $primaryKeys) as $key => $value) {
                $query->where($key, $value);
            }

            $query->limit(1)->delete();
        }

        $this->triggerEvent('afterDelete');
    }

    protected function buildQuery(): QueryBuilder
    {
        if ($this->connection) {
            return static::query($this->connection);
        }
        $connectionName = $this->meta->connectionName();

        return static::query(db($connectionName));
    }
}
