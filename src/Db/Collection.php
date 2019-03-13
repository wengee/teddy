<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-02 14:56:41 +0800
 */
namespace Teddy\Db;

use Illuminate\Support\Collection as CollectionBase;
use Teddy\Db\Traits\HasAttributes;

class Collection extends CollectionBase
{
    use HasAttributes;

    public function offsetGet($key)
    {
        return $this->getAttribute($key);
    }

    public function offsetSet($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __set($key, $value)
    {
        return $this->offsetSet($key, $value);
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __sleep()
    {
        return ['items', 'isNewRecord'];
    }

    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, array_filter($this->items, function ($key) {
            return !is_string($key) || $key{0} !== '_';
        }, ARRAY_FILTER_USE_KEY));
    }
}
