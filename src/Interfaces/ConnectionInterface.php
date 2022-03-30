<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Interfaces;

use Teddy\Pool\Pool;

interface ConnectionInterface
{
    public function setPool(Pool $pool);

    public function connect();

    public function reconnect();

    public function close();

    public function check();

    public function release();
}
