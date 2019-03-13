<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 17:30:42 +0800
 */
namespace Teddy\Db\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class StringColumn extends Column
{
    public function dbValue($value)
    {
        return (string) $value;
    }

    public function value($value)
    {
        return (string) $value;
    }
}
