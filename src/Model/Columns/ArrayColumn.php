<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-09-06 11:16:32 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class ArrayColumn extends Column
{
    public function dbValue($value)
    {
        return json_encode($value ?: []);
    }

    public function value($value)
    {
        return json_decode($value, true) ?: [];
    }
}
