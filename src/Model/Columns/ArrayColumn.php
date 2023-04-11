<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-11 14:29:41 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class ArrayColumn extends Column
{
    protected $default = [];

    public function convertToDbValue($value)
    {
        return json_encode($value ?: [], JSON_UNESCAPED_UNICODE);
    }

    public function convertToPhpValue($value)
    {
        return json_decode($value, true) ?: [];
    }
}
