<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-01-26 16:59:54 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class JsonColumn extends Column
{
    protected $asObject = false;

    public function convertToDbValue($value)
    {
        return json_encode($value);
    }

    public function convertToPhpValue($value)
    {
        if (is_string($value) && $value) {
            return json_decode($value, !$this->asObject);
        }

        return null;
    }
}
