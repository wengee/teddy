<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-09 15:18:03 +0800
 */
namespace Teddy\Traits;

trait HasOptions
{
    protected function hydrate($data = []): void
    {
        foreach ($data as $key => $value) {
            $key = str_replace('.', ' ', $key);
            $method = 'set' . ucwords($key);
            if (method_exists($this, $method)) {
                call_user_func([$this, $method], $value);
            } elseif (property_exists($this, 'options')) {
                $this->options[$key] = $value;
            }
        }
    }
}
