<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-02 14:43:05 +0800
 */
namespace SlimExtra\Db\Model\Columns;

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
            }

            $t = $this->asDateTime($value);
            return $t->getTimestamp();
        }
    }
}
