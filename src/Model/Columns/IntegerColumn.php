<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-03-07 22:45:38 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class IntegerColumn extends Column
{
    protected $default = 0;

    public function dbValue($value)
    {
        return (int) $value;
    }

    public function value($value)
    {
        return (int) $value;
    }
}
