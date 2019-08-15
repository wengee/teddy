<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Interfaces;

interface ConnectionInterface
{
    public function connect();

    public function reconnect();

    public function close();

    public function check();
}
