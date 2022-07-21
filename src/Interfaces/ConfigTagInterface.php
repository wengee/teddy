<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-21 17:43:56 +0800
 */

namespace Teddy\Interfaces;

interface ConfigTagInterface
{
    public function parseValue($value);
}
