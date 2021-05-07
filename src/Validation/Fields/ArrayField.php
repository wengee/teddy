<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 14:28:39 +0800
 */

namespace Teddy\Validation\Fields;

use Teddy\Facades\Filter;

class ArrayField extends Field
{
    protected $useJson = false;

    public function json(bool $useJson = true): self
    {
        $this->useJson = $useJson;
        return $this;
    }

    protected function filterValue($value)
    {
        if ($this->useJson && !is_array($value)) {
            $value = json_decode(strval($value), true);
        }

        return Filter::sanitize($value, 'array');
    }
}
