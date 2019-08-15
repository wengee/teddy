<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
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
