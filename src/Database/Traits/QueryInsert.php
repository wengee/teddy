<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Database\Traits;

use Exception;
use Teddy\Database\SQL;

trait QueryInsert
{
    public function insert(?array $data = [], bool $returnId = false)
    {
        $this->sqlType = SQL::INSERT_SQL;
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
            throw new Exception('Missing data for insertion');
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
