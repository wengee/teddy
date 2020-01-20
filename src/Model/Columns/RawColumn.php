<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-01-20 11:02:56 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class RawColumn extends Column
{
    public function dbValue($value)
    {
        return $value;
    }

    public function value($value)
    {
        return $value;
    }
}
