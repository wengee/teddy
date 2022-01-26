<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-01-26 17:01:12 +0800
 */

namespace Teddy\Model;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Connection
{
    /** @Required */
    public $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name ?: null;
    }
}
