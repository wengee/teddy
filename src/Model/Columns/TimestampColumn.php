<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Model\Columns;

use Exception;

/**
 * @Annotation
 * @Target("CLASS")
 */
class TimestampColumn extends DateTimeColumn
{
    protected $update = false;

    public function convertToDbValue($value)
    {
        if ($this->update) {
            return time();
        } else {
            if (is_int($value)) {
                return $value;
            } elseif (empty($value)) {
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
}
