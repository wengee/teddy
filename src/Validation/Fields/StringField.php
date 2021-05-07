<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-07 17:07:45 +0800
 */

namespace Teddy\Validation\Fields;

class StringField extends Field
{
    protected $default = '';

    protected $trim = false;

    /**
     * @param bool|string $trim
     */
    public function trim($trim = true): self
    {
        $this->trim = $trim;

        return $this;
    }

    protected function filterValue($value)
    {
        $value = (string) $value;
        if ($this->trim) {
            $value = is_string($this->trim) ? trim($value, $this->trim) : trim($value);
        }

        return $value;
    }
}
