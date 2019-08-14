<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 14:39:19 +0800
 */

namespace Teddy\Database;

use Teddy\Interfaces\ConnectionInterface;

interface DbConnectionInterface
{
    public function getReadConnection(): ConnectionInterface;

    public function getWriteConnecction(): ConnectionInterface;
}
