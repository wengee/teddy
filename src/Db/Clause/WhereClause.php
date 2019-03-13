<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-07 15:29:53 +0800
 */

namespace Teddy\Db\Clause;

use Teddy\Db\RawSQL;

class WhereClause extends ClauseContainer
{
    public function where($column, $operator = null, $value = null, string $chainType = 'AND')
    {
        if ($column instanceof RawSQL) {
            $chainType = $operator ?: $chainType;
            $this->container[] = [$column, null, null, $chainType];
        } else {
            $column = $this->query->toDbColumn($column);
            if (!in_array($operator, ['>=', '>', '<=', '<', '=', '!=', '<>'])) {
                $chainType = $value ?: $chainType;
                $value = $operator;
                $operator = '=';
            }

            $this->container[] = [$column, $operator, $value, $chainType];
        }
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        $this->where($column, $operator, $value, 'OR');
    }

    public function whereBetween($column, array $values, string $chainType = 'AND', bool $not = false)
    {
        if (count($values) === 2) {
            $column = $this->query->toDbColumn($column);
            $sql = $column . ($not ? ' NOT' : '') . ' BETWEEN ? AND ?';
            $values = array_values($values);
            $this->where(new RawSQL($sql, ...$values), $chainType);
        }
    }

    public function orWhereBetween($column, array $values)
    {
        $this->whereBetween($column, $values, 'OR', false);
    }

    public function whereNotBetween($column, array $values, string $chainType = 'AND')
    {
        $this->whereBetween($column, $values, $chainType, true);
    }

    public function orWhereNotBetween($column, array $values)
    {
        $this->whereBetween($column, $values, 'OR', true);
    }

    public function whereIn($column, array $values, string $chainType = 'AND', bool $not = false)
    {
        if (!empty($values)) {
            $column = $this->query->toDbColumn($column);
            $sql = $column . ($not ? ' NOT' : '') . ' IN (' . implode(', ', array_fill(0, count($values), '?')) . ')';
            $values = array_values($values);
            $this->where(new RawSQL($sql, ...$values), $chainType);
        }
    }

    public function orWhereIn($column, array $values)
    {
        $this->whereIn($column, $values, 'OR', false);
    }

    public function whereNotIn($column, array $values, string $chainType = 'AND')
    {
        $this->whereIn($column, $values, $chainType, true);
    }

    public function orWhereNotIn($column, array $values)
    {
        $this->whereIn($column, $values, 'OR', true);
    }

    public function whereLike($column, string $value, string $chainType = 'AND', bool $not = false)
    {
        $column = $this->query->toDbColumn($column);
        $sql = $column . ($not ? ' NOT' : '') . ' LIKE ?';
        $this->where(new RawSQL($sql, $value), $chainType);
    }

    public function orWhereLike($column, string $value)
    {
        $this->whereLike($column, $value, 'OR', false);
    }

    public function whereNotLike($column, string $value, string $chainType = 'AND')
    {
        $this->whereLike($column, $value, $chainType, true);
    }

    public function orWhereNotLike($column, string $value)
    {
        $this->whereLike($column, $value, 'OR', true);
    }

    public function whereNull($column, string $chainType = 'AND', bool $not = false)
    {
        $column = $this->query->toDbColumn($column);
        $sql = $column . ' IS' . ($not ? ' NOT' : '') . ' NULL';
        $this->where(new RawSQL($sql), $chainType);
    }

    public function orWhereNull($column)
    {
        $this->whereNull($column, 'OR', false);
    }

    public function whereNotNull($column, string $chainType = 'AND')
    {
        $this->whereNull($column, $chainType, true);
    }

    public function orWhereNotNull($column)
    {
        $this->whereNull($column, 'OR', true);
    }

    public function toSql(&$map = []): string
    {
        if (empty($this->container)) {
            return '';
        }

        $ret = '';
        foreach ($this->container as $where) {
            list($column, $operator, $value, $chainType) = $where;
            if (empty($ret)) {
                $ret .= ' WHERE ';
            } else {
                $ret .= $chainType ? " {$chainType} " : ' AND ';
            }

            if ($column instanceof RawSQL) {
                $ret .= $column->toSql($map, $this->query);
            } else {
                $ret .= "{$column} {$operator} ?";
                $map[] = $value;
            }
        }

        return $ret;
    }
}
