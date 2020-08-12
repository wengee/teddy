<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-12 15:07:18 +0800
 */

namespace Teddy;

abstract class Controller
{
    final public function __construct()
    {
        $this->initialize();
    }

    final public function __get($name)
    {
        return app($name);
    }

    public function initialize(): void
    {
    }
}
