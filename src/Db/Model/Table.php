<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-02 14:39:25 +0800
 */
namespace SlimExtra\Db\Model;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Table
{
    /** @Required */
    public $name;

    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->name = $values['value'];
        } else {
            $this->name = $values['name'] ?? null;
        }
    }
}
