<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 13:04:26 +0800
 */

namespace Teddy\Model\Columns;

use Teddy\Facades\Filter;

/**
 * @Annotation
 * @Target("CLASS")
 */
class ListColumn extends Column
{
    protected $default = [];

    protected $separator = '|';

    protected $filter;

    public function convertToDbValue($value)
    {
        return is_array($value) ? implode($this->separator, $value) : '';
    }

    public function convertToPhpValue($value)
    {
        if (is_string($value) && strlen($value) > 0) {
            $arr = explode($this->separator, $value);

            if ($this->filter) {
                $arr = array_map(function ($item) {
                    return Filter::sanitize($item, $this->filter);
                }, $arr);
            }

            return $arr;
        }

        return [];
    }
}
