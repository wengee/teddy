<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 11:11:18 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class BinaryColumn extends Column
{
    public function dbValue($value)
    {
        return @serialize($value);
    }

    public function value($value)
    {
        return $value ? unserialize($value) : null;
    }
}
