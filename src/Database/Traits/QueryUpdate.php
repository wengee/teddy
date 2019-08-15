<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Database\Traits;

use Teddy\Database\RawSQL;
use Teddy\Database\SQL;

trait QueryUpdate
{
    public function set(string $column, $operator, $value = null)
    {
        $column = $this->toDbColumn($column);
        if (in_array($operator, ['+', '-', '*', '/'], true)) {
            $this->data[] = new RawSQL("$column = $column $operator ?", $value);
        } else {
            $this->data[$column] = $operator;
        }

        return $this;
    }

    public function increase(string $column, int $n = 0)
    {
        $this->set($column, '+', $n);
        return $this;
    }

    public function decrease(string $column, int $n = 0)
    {
        $this->set($column, '-', $n);
        return $this;
    }

    public function update(?array $data = []): int
    {
        $this->sqlType = SQL::UPDATE_SQL;
        if (!empty($data)) {
            $this->setData($data);
        }

        return $this->execute();
    }

    protected function getUpdateSql(array &$map = []): string
    {
        if (empty($this->data)) {
            throw new \Exception('Missing data for update');
        }

        $sql = 'UPDATE ' . $this->getTable();
        $sql .= $this->getUpdateData($map);
        $sql .= $this->whereClause ? $this->whereClause->toSql($map) : '';
        $sql .= $this->orderClause ? $this->orderClause->toSql($map) : '';
        $sql .= $this->limitClause ? $this->limitClause->toSql($map) : '';

        return $sql;
    }

    protected function getUpdateData(array &$map = []): string
    {
        $args = [];
        foreach ($this->data as $key => $value) {
            if ($value instanceof RawSQL) {
                $value = $value->toSql($map, $this);

                if (is_int($key)) {
                    $args[] = $value;
                } else {
                    $args[] = "{$key} = {$value}";
                }
            } else {
                $args[] = "{$key} = ?";
                $map[] = $value;
            }
        }

        return ' SET ' . implode(', ', $args);
    }
}
