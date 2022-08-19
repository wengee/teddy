<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-19 10:59:03 +0800
 */

namespace Teddy\Database;

use Teddy\Database\Traits\DatabaseTrait;

class Transaction implements DatabaseInterface
{
    use DatabaseTrait;

    /**
     * @var PDOConnection
     */
    protected $pdoConnection;

    public function __construct(PDOConnection $pdoConnection)
    {
        $this->pdoConnection = $pdoConnection;
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
