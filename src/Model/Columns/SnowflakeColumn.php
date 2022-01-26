<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-01-26 17:00:09 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
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
