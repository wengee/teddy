<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-26 14:20:17 +0800
 */

namespace Teddy;

use ArrayAccess;

class Options implements ArrayAccess
{
    protected $strict = true;

    protected $data = [];

    public function __construct(array $data = [], bool $strict = true)
    {
        $this->data = $data;
        $this->strict = $strict;
    }

    public function update(array $data, array ...$args): self
    {
        $data = array_merge($data, ...$args);
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    public function __get($key)
    {
        return $this->get(strval($key));
    }

    public function __set($key, $value)
    {
        return $this->set(strval($key), $value);
    }

    public function __isset($key)
    {
        return $this->has(strval($key));
    }

    public function __unset($key)
    {
        return $this->remove(strval($key));
    }

    public function offsetExists($offset)
    {
        return $this->has(strval($offset));
    }

    public function offsetGet($offset)
    {
        return $this->get(strval($offset));
    }

    public function offsetSet($offset, $value)
    {
        return $this->set(strval($offset), $value);
    }

    public function offsetUnset($offset)
    {
        return $this->remove(strval($offset));
    }

    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function set(string $key, $value)
    {
        if (!$this->strict || array_key_exists($key, $this->data)) {
            return $this->data[$key] = $value;
        }
    }

    public function has(string $key)
    {
        return array_key_exists($key, $this->data);
    }

    public function remove(string $key): void
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
    }
}
