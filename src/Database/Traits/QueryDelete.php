<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Database\Traits;

use Teddy\Database\SQL;

trait QueryDelete
{
    public function delete(): int
    {
        $this->sqlType = SQL::DELETE_SQL;
        return $this->execute();
    }

    protected function getDeleteSql(array &$map = []): string
    {
        $sql = 'DELETE FROM ' . $this->getTable();
        $sql .= $this->whereClause ? $this->whereClause->toSql($map) : '';
        $sql .= $this->orderClause ? $this->orderClause->toSql($map) : '';
        $sql .= $this->limitClause ? $this->limitClause->toSql($map) : '';

        return $sql;
    }
}
