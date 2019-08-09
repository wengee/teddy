<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 11:11:37 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class FloatColumn extends Column
{
    public function dbValue($value)
    {
        return (float) $value;
    }

    public function value($value)
    {
        return (float) $value;
    }
}
