<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-01-26 16:59:09 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ArrayColumn extends Column
{
    protected $default = [];

    public function convertToDbValue($value)
    {
        return json_encode($value ?: []);
    }

    public function convertToPhpValue($value)
    {
        return json_decode($value, true) ?: [];
    }
}
