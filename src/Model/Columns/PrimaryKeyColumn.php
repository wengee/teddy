<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-05 10:53:52 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class PrimaryKeyColumn extends Column
{
    protected $name = 'id';

    protected $primaryKey = true;

    protected $autoIncrement = true;

    public function convertToDbValue($value)
    {
        if (null === $value) {
            return null;
        }

        return (int) $value;
    }

    public function convertToPhpValue($value)
    {
        return (int) $value;
    }
}
