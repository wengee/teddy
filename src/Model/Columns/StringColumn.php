<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-03-08 10:27:10 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class StringColumn extends Column
{
    protected $default = '';

    protected $length = 0;

    public function convertToDbValue($value)
    {
        $value = strval($value);
        if ($this->length > 0) {
            $value = substr($value, 0, $this->length);
        }

        return $value;
    }

    public function convertToPhpValue($value)
    {
        return (string) $value;
    }
}
