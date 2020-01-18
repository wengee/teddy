<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-01-18 17:34:47 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class SnowflakeColumn extends Column
{
    protected $primaryKey = true;

    public function dbValue($value)
    {
        if (!$value) {
            return app('snowflake')->id();
        }

        return (int) $value;
    }

    public function value($value)
    {
        return (int) $value;
    }
}
