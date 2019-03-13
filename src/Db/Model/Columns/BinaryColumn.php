<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 17:30:07 +0800
 */
namespace Teddy\Db\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class BinaryColumn extends Column
{
    public function dbValue($value)
    {
        return @serialize($value);
    }

    public function value($value)
    {
        return $value ? unserialize($value) : null;
    }
}
