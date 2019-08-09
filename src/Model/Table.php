<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 11:11:13 +0800
 */

namespace Teddy\Model;

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
