<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-06-28 17:16:18 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class AmountColumn extends Column
{
    protected $default = 0;

    protected $times = 100;

    public function convertToDbValue($value)
    {
        return intval($value * $this->times);
    }

    public function convertToPhpValue($value)
    {
        return intval($value) / $this->times;
    }
}
