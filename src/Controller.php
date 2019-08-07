<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-06 15:05:42 +0800
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
