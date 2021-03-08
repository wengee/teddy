<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-03-08 10:26:56 +0800
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

    public function convertToDbValue($value)
    {
        return (int) $value;
    }

    public function convertToPhpValue($value)
    {
        return (int) $value;
    }
}
