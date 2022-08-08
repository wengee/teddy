<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:42:13 +0800
 */

namespace Teddy\Model;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Connection
{
    /**
     * @var string
     */
    protected $name = '';

    public function __construct(?string $name = null)
    {
        $this->name = $name ?: '';
    }

    public function getName(): string
    {
        return $this->name;
    }
}
