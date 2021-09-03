<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class SnowflakeColumn extends Column
{
    public function convertToDbValue($value)
    {
        if (!$value) {
            return app('snowflake')->id();
        }

        return (int) $value;
    }

    public function convertToPhpValue($value)
    {
        return (int) $value;
    }
}
