<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 16:38:37 +0800
 */

namespace Teddy\Validation\Fields;

class AnyField extends Field
{
    protected function filterValue($value)
    {
        return $value;
    }
}
