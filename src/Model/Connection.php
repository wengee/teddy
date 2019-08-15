<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Model;

/**
 * @Annotation
 * @Target("CLASS")
 */
class Connection
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
