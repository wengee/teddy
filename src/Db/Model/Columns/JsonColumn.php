<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 17:30:37 +0800
 */
namespace Teddy\Db\Model\Columns;

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
