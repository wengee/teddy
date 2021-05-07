<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-07 17:07:25 +0800
 */

namespace Teddy\Validation\Fields;

use Teddy\Facades\Filter;

class IntegerField extends Field
{
    protected $default = 0;

    protected function filterValue($value)
    {
        return Filter::sanitize($value, 'int');
    }
}
