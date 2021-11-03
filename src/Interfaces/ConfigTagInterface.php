<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-11-03 11:38:36 +0800
 */

namespace Teddy\Interfaces;

interface ConfigTagInterface
{
    public function __construct($value);

    public function getValue();
}
