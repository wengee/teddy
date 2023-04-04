<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-04-04 17:50:58 +0800
 */

namespace Teddy\Database\DBAL;

use Doctrine\DBAL\Driver\AbstractSQLiteDriver;
use Doctrine\DBAL\Driver\PDO\Connection;
use PDO;

class SqliteDriver extends AbstractSQLiteDriver
{
    public function connect(array $params)
    {
        if (!isset($params['pdo']) || !$params['pdo'] instanceof PDO) {
            throw new \InvalidArgumentException('The "pdo" property must be required.');
        }

        return new Connection($params['pdo']);
    }
}
