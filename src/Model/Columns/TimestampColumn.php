<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-29 17:01:42 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;
use Exception;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class TimestampColumn extends DateTimeColumn
{
    protected $update = false;

    public function convertToDbValue($value)
    {
        if ($this->update) {
            return time();
        }

        if (is_int($value)) {
            return $value;
        }

        if (empty($value)) {
            return 0;
        }

        try {
            $t = $this->asDateTime($value);
        } catch (Exception $e) {
            return 0;
        }

        return $t->getTimestamp();
    }
}
