<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 11:11:55 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class StringColumn extends Column
{
    protected $length = 0;

    public function dbValue($value)
    {
        $value = strval($value);
        if ($this->length > 0) {
            $value = substr($value, 0, $this->length);
        }

        return $value;
    }

    public function value($value)
    {
        return (string) $value;
    }
}