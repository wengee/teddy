<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-30 11:15:41 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class TimestampColumn extends Column
{
    protected $update = false;

    public function convertToDbValue($value)
    {
        if ($this->update) {
            return time();
        }

        return (int) $value;
    }

    public function convertToPhpValue($value)
    {
        return (int) $value;
    }

    public function defaultValue()
    {
        if ('now' === $this->default) {
            return time();
        }

        return (int) $this->default;
    }
}
