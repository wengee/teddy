<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 16:18:18 +0800
 */

namespace Teddy\Interfaces;

interface ConnectionInterface
{
    public function connect();

    public function reconnect();

    public function close();

    public function check();
}
