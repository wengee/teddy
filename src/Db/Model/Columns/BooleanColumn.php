<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-25 18:34:42 +0800
 */
namespace Teddy\Db\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class BooleanColumn extends Column
{
    public function dbValue($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
    }

    public function value($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
