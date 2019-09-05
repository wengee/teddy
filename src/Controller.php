<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-09-05 15:14:47 +0800
 */

namespace Teddy;

abstract class Controller
{
    final public function __construct()
    {
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    final public function __get($name)
    {
        return app($name);
    }
}
