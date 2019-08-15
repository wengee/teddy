<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Database;

use Teddy\Interfaces\ConnectionInterface;

class Transaction implements DbConnectionInterface
{
    protected $pdoConnection;

    public function __construct(PDOConnection $pdoConnection)
    {
        $this->pdoConnection = $pdoConnection;
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }

    public function getReadConnection(): ConnectionInterface
    {
        return $this->pdoConnection;
    }

    public function getWriteConnecction(): ConnectionInterface
    {
        return $this->pdoConnection;
    }
}
