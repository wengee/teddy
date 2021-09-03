<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Abstracts;

use ArrayAccess;

abstract class AbstractOptions implements ArrayAccess
{
    protected $keys;

    protected $data = [];

    public function __construct(?array $data = null)
    {
        $this->initialize();

        if ($data) {
            $this->update($data);
            $this->keys = $this->keys ?: array_keys($this->data);
        }
    }

    public function __get($key)
    {
        return $this->get(strval($key));
    }

    public function __set($key, $value): void
    {
        $this->set(strval($key), $value);
    }

    public function __isset($key)
    {
        return $this->has(strval($key));
    }

    public function __unset($key): void
    {
        $this->remove(strval($key));
    }

    public function setKeys(array $keys): void
    {
        $this->keys = $keys;
    }

    public function update(array $data, array ...$args): void
    {
        $data = array_merge($data, ...$args);
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function offsetExists($offset)
    {
        return $this->has(strval($offset));
    }

    public function offsetGet($offset)
    {
        return $this->get(strval($offset));
    }

    public function offsetSet($offset, $value): void
    {
        $this->set(strval($offset), $value);
    }

    public function offsetUnset($offset): void
    {
        $this->remove(strval($offset));
    }

    public function get(string $key, $default = null)
    {
        $method = 'get'.ucwords($key);
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        if (!$this->has($key)) {
            return $default;
        }

        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $method = 'set'.ucwords($key);
        if (method_exists($this, $method)) {
            $this->{$method}($value);
        } elseif ($this->has($key)) {
            $this->data[$key] = $value;
        }
    }

    public function has(string $key)
    {
        return !$this->keys || in_array($key, $this->keys);
    }

    public function remove(string $key): void
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
    }

    public function push(string $key, ...$values): void
    {
        $arr = $this->get($key, []);
        $arr = is_array($arr) ? $arr : [$arr];
        array_push($arr, ...$values);
        $this->set($key, $arr);
    }

    protected function initialize(): void
    {
    }
}
