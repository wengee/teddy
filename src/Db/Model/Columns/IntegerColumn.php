<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 17:30:34 +0800
 */
namespace SlimExtra\Db\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class IntegerColumn extends Column
{
    public function dbValue($value)
    {
        return (int) $value;
    }

    public function value($value)
    {
        return (int) $value;
    }
}
