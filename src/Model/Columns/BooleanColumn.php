<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 11:11:21 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class BooleanColumn extends Column
{
    public function dbValue($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    public function value($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
