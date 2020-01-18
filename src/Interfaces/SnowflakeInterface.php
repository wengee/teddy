<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-01-18 17:37:42 +0800
 */

namespace Teddy\Interfaces;

interface SnowflakeInterface
{
    public function id(): int;
}
