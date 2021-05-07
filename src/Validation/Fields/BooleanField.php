<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 14:18:07 +0800
 */

namespace Teddy\Validation\Fields;

use Teddy\Facades\Filter;

class BooleanField extends Field
{
    protected function filterValue($value)
    {
        return Filter::sanitize($value, 'bool');
    }
}
