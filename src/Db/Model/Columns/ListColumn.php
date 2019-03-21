<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-21 17:06:10 +0800
 */
namespace Teddy\Db\Model\Columns;

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
