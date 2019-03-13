<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-02 14:44:47 +0800
 */
namespace SlimExtra\Db\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class PrimaryKeyColumn extends Column
{
    protected $name = 'id';

    protected $primaryKey = true;

    protected $autoIncrement = true;

    public function dbValue($value)
    {
        return (int) $value;
    }

    public function value($value)
    {
        return (int) $value;
    }
}
