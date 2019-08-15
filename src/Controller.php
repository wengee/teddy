<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy;

abstract class Controller
{
    final public function __construct()
    {
    }

    final public function __get($name)
    {
        return app($name);
    }
}
