<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class JsonColumn extends Column
{
    protected $asObject = false;

    public function convertToDbValue($value)
    {
        return json_encode($value);
    }

    public function convertToPhpValue($value)
    {
        if (is_string($value) && $value) {
            return json_decode($value, !$this->asObject);
        }

        return null;
    }
}
