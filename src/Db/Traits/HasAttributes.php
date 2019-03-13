<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-02 15:01:25 +0800
 */

namespace SlimExtra\Db\Traits;

use Illuminate\Support\Str;

trait HasAttributes
{
    public function assign(array $data)
    {
        foreach ($data as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function setAttribute($key, $value)
    {
        $this->checkKey($key);
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        if ($key === null) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    public function getAttribute($key, $default = null)
    {
        $this->checkKey($key);
        if ($this->hasGetMutator($key)) {
            return $this->getMutatedAttributeValue($key);
        }

        if (array_key_exists($key, $this->items)) {
            return $this->items[$key];
        }

        return $default;
    }

    public function hasSetMutator($key)
    {
        return method_exists($this, 'set' . Str::studly($key) . 'Attribute');
    }

    protected function setMutatedAttributeValue($key, $value)
    {
        return $this->{'set' . Str::studly($key) . 'Attribute'}($value);
    }

    public function hasGetMutator($key)
    {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute');
    }

    protected function getMutatedAttributeValue($key)
    {
        return $this->{'get' . Str::studly($key) . 'Attribute'}();
    }

    protected function checkKey($key)
    {
        if (is_string($key) && $key{0} === '_') {
            throw new \Exception('The property is not public.');
        }
    }
}
