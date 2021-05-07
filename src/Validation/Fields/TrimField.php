<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-07 14:38:55 +0800
 */

namespace Teddy\Validation\Fields;

class TrimField extends StringField
{
    protected $trim = true;
}
