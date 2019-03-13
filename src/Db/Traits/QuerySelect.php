<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 15:06:21 +0800
 */

namespace SlimExtra\Db\Traits;

use SlimExtra\Db\Database;
use SlimExtra\Db\Paginator;
use SlimExtra\Db\RawSQL;

trait QuerySelect
{
    /**
     * @var boolean
     */
    protected $distinct = false;

    /**
     * @var array
     */
    protected $columns = ['*'];

    public function select(...$columns)
    {
        $this->sqlType = Database::SELECT_SQL;
        if (!empty($columns)) {
            $this->setColumns(...$columns);
        }

        return $this;
    }

    public function distinct(bool $distinct = true)
    {
        $this->distinct = $distinct;
        return $this;
    }

    public function first()
    {
        return $this->fetch();
    }

    public function all()
    {
        return $this->fetchAll();
    }

    public function fetch()
    {
        return $this->select()->execute([
            'fetchType' => Database::FETCH_ONE,
        ]);
    }

    public function fetchAll(int $limit = 0, int $offset = 0)
    {
        if ($limit > 0) {
            $this->limit($limit, $offset);
        }

        return $this->select()->execute([
            'fetchType' => Database::FETCH_ALL,
        ]);
    }

    public function fetchColumn()
    {
        return $this->select()->execute([
            'fetchType' => Database::FETCH_COLUMN,
        ]);
    }

    public function paginate(int $page = 1, int $pageSize = 20)
    {
        $countQuery = clone $this;
        $total = $countQuery->count();

        $page = max($page, 1);
        $pageSize = max($pageSize, 1);

        $offset = ($page - 1) * $pageSize;
        if ($total > $offset) {
            $items = $this->fetchAll($pageSize, $offset);
        } else {
            $items = [];
        }

        return new Paginator($items, $total, $pageSize, $page);
    }

    public function count(string $column = '*', string $as = null)
    {
        $sql = 'COUNT(' . $this->toDbColumn($column) . ')';
        $this->columns = [new RawSQL($sql)];
        return $this->fetchColumn();
    }

    protected function setColumns(...$columns)
    {
        if (!empty($columns)) {
            $this->columns = [];

            foreach ($columns as $column) {
                $column = $this->toDbColumn($column);

                if (is_array($column)) {
                    $this->columns = array_merge($this->columns, $column);
                } else {
                    $this->columns[] = $column;
                }
            }
        }

        return $this;
    }

    protected function getSelectSql(array &$map = []): string
    {
        $sql = 'SELECT ';

        if ($this->distinct) {
            $sql .= ' DISTINCT ';
        }

        $sql .= $this->getSelectColumns($this->columns, $map);
        $sql .= ' FROM ' . $this->getTable();
        $sql .= $this->joinClause ? $this->joinClause->toSql($map) : '';
        $sql .= $this->whereClause ? $this->whereClause->toSql($map) : '';
        $sql .= $this->groupClause ? $this->groupClause->toSql($map) : '';
        $sql .= $this->havingClause ? $this->havingClause->toSql($map) : '';
        $sql .= $this->orderClause ? $this->orderClause->toSql($map) : '';
        $sql .= $this->limitClause ? $this->limitClause->toSql($map) : '';

        return $sql;
    }

    protected function getSelectColumns(array $columns, array &$map = [])
    {
        if (empty($columns)) {
            return '*';
        }

        $ret = [];
        foreach ($columns as $key => $value) {
            if (is_int($key)) {
                $ret[] = ($value instanceof RawSQL) ? $value->toSql($map, $this) : $value;
            } else {
                $key = ($key instanceof RawSQL) ? $key->toSql($map, $this) : $key;
                $ret[] = "{$key} AS {$value}";
            }
        }

        return implode(', ', $ret);
    }
}
