<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-09 17:49:20 +0800
 */

namespace Teddy\Db\Traits;

use Teddy\Db\Database;

trait QueryInsert
{
    public function insert(?array $data = [], bool $returnId = false)
    {
        $this->sqlType = Database::INSERT_SQL;
        if (!empty($data)) {
            $this->setData($data);
        }

        return $this->execute([
            'lastInsertId' => $returnId,
        ]);
    }

    protected function getInsertSql(array &$map = []): string
    {
        if (empty($this->data)) {
            throw new \Exception('Missing data for insertion');
        }

        $sql = 'INSERT INTO ' . $this->getTable();
        $sql .= $this->getInsertData($map);

        return $sql;
    }

    protected function getInsertData(array &$map = []): string
    {
        $columns = [];
        $placeholders = [];
        foreach ($this->data as $key => $value) {
            $columns[] = $key;
            if ($value instanceof RawSQL) {
                $placeholders[] = $value->toSql($map, $this);
            } else {
                $placeholders[] = '?';
                $map[] = $value;
            }
        }

        return ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
    }
}
