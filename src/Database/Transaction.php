<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Database;

class Transaction implements DatabaseInterface
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

    public function getReadConnection(): DbConnectionInterface
    {
        return $this->pdoConnection;
    }

    public function getWriteConnection(): DbConnectionInterface
    {
        return $this->pdoConnection;
    }
}
