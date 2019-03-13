<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 17:30:19 +0800
 */
namespace SlimExtra\Db\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class BooleanColumn extends Column
{
    public function dbValue($value)
    {
        return $value ? 1 : 0;
    }

    public function value($value)
    {
        return (bool) $value;
    }
}
