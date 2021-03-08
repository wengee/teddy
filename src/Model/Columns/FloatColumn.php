<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-03-08 10:26:38 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class FloatColumn extends Column
{
    protected $default = 0.0;

    public function convertToDbValue($value)
    {
        return (float) $value;
    }

    public function convertToPhpValue($value)
    {
        return (float) $value;
    }
}
