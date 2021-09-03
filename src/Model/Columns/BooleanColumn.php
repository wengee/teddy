<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class BooleanColumn extends Column
{
    protected $default = false;

    public function convertToDbValue($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    public function convertToPhpValue($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
