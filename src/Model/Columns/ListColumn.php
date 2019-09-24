<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-09-24 11:12:51 +0800
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
        if (is_string($value) && strlen($value) > 0) {
            return explode($this->separator, $value);
        }

        return [];
    }
}
