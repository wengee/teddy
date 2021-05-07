<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-07 17:07:39 +0800
 */

namespace Teddy\Validation\Fields;

use Teddy\Facades\Filter;

class ListField extends Field
{
    protected $default = [];

    protected $pattern;

    /**
     * @param null|bool|string $pattern
     */
    public function split($pattern = true): self
    {
        if (true === $pattern) {
            $pattern = '/[\s,]+/';
        }

        $this->pattern = $pattern ? strval($pattern) : null;

        return $this;
    }

    protected function filterValue($value)
    {
        if ($this->pattern && !is_array($value)) {
            $value = preg_split($this->pattern, strval($value));
        }

        return Filter::sanitize($value, 'list');
    }
}
