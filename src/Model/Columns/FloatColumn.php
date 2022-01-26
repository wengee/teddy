<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-01-26 16:59:44 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class FloatColumn extends Column
{
    protected $default = 0.0;

    public function convertToDbValue($value)
    {
        return (float) $value;
    }

    public function convertToPhpValue($value)
    {
        return (float) $value;
    }
}
