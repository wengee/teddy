<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
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
