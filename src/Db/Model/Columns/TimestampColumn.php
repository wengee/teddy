<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-06-03 18:08:34 +0800
 */
namespace Teddy\Db\Model\Columns;

use Exception;

/**
 * @Annotation
 * @Target("CLASS")
 */
class TimestampColumn extends DateTimeColumn
{
    protected $update = false;

    public function dbValue($value)
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
