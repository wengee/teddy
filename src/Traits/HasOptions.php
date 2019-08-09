<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 17:08:31 +0800
 */

namespace Teddy\Traits;

trait HasOptions
{
    protected function setOptions($data = []): void
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
