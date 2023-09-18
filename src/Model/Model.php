<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2024-03-20 15:02:21 +0800
 */

namespace Teddy\Model;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Teddy\Database\DatabaseInterface;
use Teddy\Database\DbException;
use Teddy\Database\QueryBuilder;
use Teddy\Interfaces\ArrayableInterface;
use Teddy\Interfaces\JsonableInterface;

abstract class Model implements \ArrayAccess, \JsonSerializable
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
    protected $newRecord = true;

    /**
     * @var bool
     */
    protected $modified = false;

    /**
     * @var string
     */
    protected $tableSuffix = '';

    /**
     * @var Meta
     */
    protected $meta;

    final public function __construct(array $data = [])
    {
        $this->meta  = app('modelManager')->getMeta(static::class);
        $this->items = $this->meta->getDefaults();
        if ($data) {
            $this->assign($data);
        }
    }

    public function __serialize(): array
    {
        return [
            'items'       => $this->items,
            'isNewRecord' => $this->newRecord,
            'isModified'  => $this->modified,
            'tableSuffix' => $this->tableSuffix,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->items       = $data['items'] ?? [];
        $this->newRecord   = $data['isNewRecord'] ?? false;
        $this->modified    = $data['isModified'] ?? false;
        $this->tableSuffix = $data['tableSuffix'] ?? '';
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

            if ($value instanceof \JsonSerializable) {
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
            if (isset($keys['only'])) {
                $except = false;
                $keys   = $keys['only'] ?: [];
            } elseif (isset($keys['except'])) {
                $except = true;
                $keys   = $keys['except'] ?: [];
            }

            $values = $except ? Arr::except($values, $keys) : Arr::only($values, $keys);
        }

        return $values;
    }

    public function isNewRecord(): bool
    {
        return $this->newRecord;
    }

    public function isModified(): bool
    {
        return $this->modified;
    }

    public function assign(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * @throws DbException|\Exception
     */
    public function saveOrError(?DatabaseInterface $db = null): void
    {
        if (!$this->isNewRecord() && !$this->isModified()) {
            return;
        }

        $primaryKeys = $this->meta->getPrimaryKeys();
        if (empty($primaryKeys)) {
            throw new DbException('Primary keys is not defined.');
        }

        $this->triggerEvent('beforeSaveOrDelete');
        $this->triggerEvent('beforeSave');

        $query      = static::query($db)->tableSuffix($this->tableSuffix);
        $attributes = $this->getDbAttributes();
        if ($this->isNewRecord()) {
            $this->triggerEvent('beforeInsert');
            $autoIncrementKey = $this->meta->getAutoIncrementKey();
            $lastInsertId     = (int) $query->insert($attributes, (bool) $autoIncrementKey);
            if ($autoIncrementKey && $lastInsertId > 0) {
                $this->setAttribute($autoIncrementKey, $lastInsertId);
            }

            $this->triggerEvent('afterInsert');
            $this->newRecord = false;
        } else {
            $this->triggerEvent('beforeUpdate');
            foreach (Arr::only($attributes, $primaryKeys) as $key => $value) {
                $query->where($key, $value);
            }

            $data = Arr::except($attributes, $primaryKeys);
            $query->limit(1)->update((array) $data);
            $this->triggerEvent('afterUpdate');
        }

        $this->modified = false;
        $this->triggerEvent('afterSave');
        $this->triggerEvent('afterSaveOrDelete');
    }

    public function save(?DatabaseInterface $db = null): bool
    {
        try {
            $this->saveOrError($db);
        } catch (\Exception $e) {
            log_exception($e);

            return false;
        }

        return true;
    }

    /**
     * @throws DbException|\Exception
     */
    public function deleteOrError(?DatabaseInterface $db = null): void
    {
        $primaryKeys = $this->meta->getPrimaryKeys();
        if (empty($primaryKeys)) {
            throw new DbException('Primary keys is not defined.');
        }

        $this->triggerEvent('beforeSaveOrDelete');
        $this->triggerEvent('beforeDelete');

        $query      = static::query($db)->tableSuffix($this->tableSuffix);
        $attributes = $this->getDbAttributes();
        if (!$this->isNewRecord()) {
            foreach (Arr::only($attributes, $primaryKeys) as $key => $value) {
                $query->where($key, $value);
            }

            $query->limit(1)->delete();
        }

        $this->triggerEvent('afterDelete');
        $this->triggerEvent('afterSaveOrDelete');
    }

    public function delete(?DatabaseInterface $db = null): bool
    {
        try {
            $this->deleteOrError($db);
        } catch (\Exception $e) {
            log_exception($e);

            return false;
        }

        return true;
    }

    public static function create(string $tableSuffix = ''): static
    {
        $obj = new static();

        $obj->newRecord   = true;
        $obj->tableSuffix = $tableSuffix ?: '';

        return $obj;
    }

    public static function createFromDb(array $data = [], ?string $tableSuffix = ''): static
    {
        $obj = new static();
        $obj->setDbAttributes($data);
        $obj->newRecord   = false;
        $obj->tableSuffix = $tableSuffix ?: '';

        return $obj;
    }

    public static function query(?DatabaseInterface $db = null): QueryBuilder
    {
        if (null === $db) {
            $connectionName = app('modelManager')->getMeta(static::class)->getConnectionName();

            return new QueryBuilder(db($connectionName), static::class);
        }

        return new QueryBuilder($db, static::class);
    }

    protected function hasAttribute(string $key)
    {
        if ($this->hasGetMutator($key) || $this->hasColumn($key)) {
            return true;
        }

        return false;
    }

    protected function setAttribute(string $key, $value): void
    {
        if ($this->hasSetMutator($key)) {
            $this->modified = true;
            $this->setMutatedAttributeValue($key, $value);
        } elseif ($this->hasColumn($key)) {
            $this->modified = $this->modified || !array_key_exists($key, $this->items) || ($this->items[$key] !== $value);

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
}
