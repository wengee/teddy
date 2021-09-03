<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
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
