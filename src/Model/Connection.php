<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 11:16:17 +0800
 */

namespace Teddy\Model;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Connection
{
    protected ?string $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name ?: null;
    }

    public function getName(): string
    {
        return $this->name ?: '';
    }
}
