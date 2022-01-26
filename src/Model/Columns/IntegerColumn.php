<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-01-26 16:59:51 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class IntegerColumn extends Column
{
    protected $default = 0;

    public function convertToDbValue($value)
    {
        return (int) $value;
    }

    public function convertToPhpValue($value)
    {
        return (int) $value;
    }
}
