<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-01-26 16:59:59 +0800
 */

namespace Teddy\Model\Columns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
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
                    return app('filter')->sanitize($item, $this->filter);
                }, $arr);
            }

            return $arr;
        }

        return [];
    }
}
