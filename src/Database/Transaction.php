<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-17 21:18:17 +0800
 */

namespace Teddy\Database;

class Transaction implements DatabaseInterface
{
    /**
     * @var PDOConnection
     */
    protected $pdoConnection;

    public function __construct(PDOConnection $pdoConnection)
    {
        $this->pdoConnection = $pdoConnection;
    }

    public function table(string $table, ?string $suffix = null): QueryBuilder
    {
        return new QueryBuilder($this, $table, $suffix);
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
