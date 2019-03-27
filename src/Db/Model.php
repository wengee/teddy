<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-27 18:19:39 +0800
 */
namespace Teddy\Db;

abstract class Model extends Collection
{
    protected $isNewRecord = true;

    protected $metaInfo;

    public function tableName(): string
    {
        return '';
    }

    public function isNewRecord(): bool
    {
        return $this->isNewRecord;
    }

    public function save($arg = null)
    {
        $metaInfo = $this->metaInfo();
        $primaryKeys = $metaInfo->primaryKeys();
        if (empty($primaryKeys)) {
            throw new DbException('Primary keys is not defined.');
        }

        $this->trigger('beforeSave');
        $query = static::query($arg);
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
        return true;
    }

    public function delete()
    {
        $primaryKeys = $this->metaInfo()->primaryKeys();
        if (empty($primaryKeys)) {
            throw new DbException('Primary keys is not defined.');
        }

        $this->trigger('beforeDelete');
        $query = static::query($arg);
        if (!$this->isNewRecord()) {
            foreach (array_only($attributes, $primaryKeys) as $key => $value) {
                $query->where($key, $value);
            }

            $query->limit(1)->delete();
        }

        $this->trigger('afterDelete');
        return true;
    }

    public static function query(...$args): QueryBuilder
    {
        if (isset($args[0]) && ($args[0] instanceof QueryInterface)) {
            $query = new QueryBuilder($args[0], static::class);
        } else {
            $query = new QueryBuilder(app('db'), static::class);
        }

        if (isset($args[0]) && is_string($args[0])) {
            $query->as($args[0]);
        } elseif (isset($args[1]) && is_string($args[1])) {
            $query->as($args[1]);
        }

        return $query;
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

    public static function __callStatic($method, $params)
    {
        if (starts_with($method, 'fetchBy')) {
            $property = lcfirst(substr($method, 7));
            $value = $params[0] ?? null;
            return static::query()->where($property, $value)->first();
        }

        throw new DbException("The method '{$method}' is undefined for this class.");
    }

    protected function trigger(string $action, ...$args)
    {
        if (\method_exists($this, $action)) {
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
}
