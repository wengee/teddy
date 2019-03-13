<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-09 17:47:35 +0800
 */

namespace SlimExtra\Db\Traits;

use SlimExtra\Db\Database;

trait QueryDelete
{
    public function delete(): int
    {
        $this->sqlType = Database::DELETE_SQL;
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
