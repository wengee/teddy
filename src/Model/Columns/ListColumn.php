<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 11:11:49 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class ListColumn extends Column
{
    protected $separator = '|';

    public function dbValue($value)
    {
        return is_array($value) ? implode($this->separator, $value) : '';
    }

    public function value($value)
    {
        return is_string($value) ? explode($this->separator, $value) : [];
    }
}
