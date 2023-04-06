<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-06 22:12:05 +0800
 */

namespace Teddy\Database;

use Teddy\Database\Traits\DatabaseTrait;

class Transaction implements DatabaseInterface
{
    use DatabaseTrait;

    protected PDOConnection $pdoConnection;

    public function __construct(PDOConnection $pdoConnection)
    {
        $this->pdoConnection = $pdoConnection;
    }

    public function getDriver(): string
    {
        return $this->pdoConnection->getDriver();
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
