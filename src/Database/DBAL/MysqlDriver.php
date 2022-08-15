<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-15 16:52:51 +0800
 */

namespace Teddy\Database\DBAL;

use Doctrine\DBAL\Driver\AbstractMySQLDriver;
use Doctrine\DBAL\Driver\PDO\Connection;
use PDO;

class MysqlDriver extends AbstractMySQLDriver
{
    public function connect(array $params)
    {
        if (!isset($params['pdo']) || !$params['pdo'] instanceof PDO) {
            throw new \InvalidArgumentException('The "pdo" property must be required.');
        }

        return new Connection($params['pdo']);
    }
}
