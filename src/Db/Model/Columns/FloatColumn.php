<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 17:30:32 +0800
 */
namespace Teddy\Db\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class FloatColumn extends Column
{
    public function dbValue($value)
    {
        return (float) $value;
    }

    public function value($value)
    {
        return (float) $value;
    }
}
