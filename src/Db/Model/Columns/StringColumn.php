<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 17:30:42 +0800
 */
namespace Teddy\Db\Model\Columns;

/**
 * @Annotation
 * @Target("CLASS")
 */
class StringColumn extends Column
{
    protected $length = 0;

    public function dbValue($value)
    {
        $value = strval($value);
        if ($this->length > 0) {
            $value = substr($value, 0, $this->length);
        }

        return $value;
    }

    public function value($value)
    {
        return (string) $value;
    }
}
