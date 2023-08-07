<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-07-18 22:01:53 +0800
 */

namespace Teddy\Database\DBAL;

use Doctrine\DBAL\Driver\AbstractPostgreSQLDriver;
use Doctrine\DBAL\Driver\PDO\Connection;
use PDO;

class PgsqlDriver extends AbstractPostgreSQLDriver
{
    public function connect(array $params)
    {
        if (!isset($params['pdo']) || !$params['pdo'] instanceof PDO) {
            throw new \InvalidArgumentException('The "pdo" property must be required.');
        }

        return new Connection($params['pdo']);
    }
}
