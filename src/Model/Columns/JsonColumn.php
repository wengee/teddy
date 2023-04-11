<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-11 14:20:47 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class JsonColumn extends Column
{
    protected $asObject = false;

    protected $flags = JSON_UNESCAPED_UNICODE;

    protected $depth = 512;

    public function convertToDbValue($value)
    {
        return json_encode($value, $this->flags, $this->depth);
    }

    public function convertToPhpValue($value)
    {
        if (is_string($value) && $value) {
            return json_decode($value, !$this->asObject, $this->depth);
        }

        return null;
    }
}
