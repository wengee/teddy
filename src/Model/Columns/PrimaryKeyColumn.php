<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 11:11:52 +0800
 */

namespace Teddy\Model\Columns;

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
