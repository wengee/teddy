<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 11:06:05 +0800
 */

namespace Teddy\Database;

class Transaction implements DatabaseInterface
{
    protected PDOConnection $pdoConnection;

    public function __construct(PDOConnection $pdoConnection)
    {
        $this->pdoConnection = $pdoConnection;
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }

    public function getReadConnection(): DbConnectionInterface
    {
        return $this->pdoConnection;
    }

    public function getWriteConnection(): DbConnectionInterface
    {
        return $this->pdoConnection;
    }
}
