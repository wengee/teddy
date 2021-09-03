<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Validation\Validators;

class DateValidator extends DateTimeValidator
{
    protected $format = 'Y-m-d';

    protected $message = ':label不是合法的日期格式';
}
