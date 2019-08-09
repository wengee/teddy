<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 11:11:46 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class JsonColumn extends Column
{
    protected $asObject = false;

    public function dbValue($value)
    {
        return json_encode($value);
    }

    public function value($value)
    {
        return json_decode($value, !$this->asObject);
    }
}
