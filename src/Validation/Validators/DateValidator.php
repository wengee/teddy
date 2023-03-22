<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:29:57 +0800
 */

namespace Teddy\Validation\Validators;

class DateValidator extends DateTimeValidator
{
    protected string $format = 'Y-m-d';

    protected string $message = ':label不是合法的日期格式';
}
