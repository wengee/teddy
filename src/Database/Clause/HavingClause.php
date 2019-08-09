<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-05-09 14:10:34 +0800
 */

namespace Teddy\Database\Clause;

use Teddy\Database\RawSQL;

class HavingClause extends ClauseContainer
{
    public function having($column, $operator, $value = null, string $chainType = 'AND')
    {
        if ($column instanceof RawSQL) {
            $chainType = $operator ?: $chainType;
            $this->container[] = [$column, null, null, $chainType];
        } else {
            $column = $this->query->toDbColumn($column);
            if (!in_array($operator, ['>=', '>', '<=', '<', '=', '!=', '<>'], true)) {
                $chainType = $value ?: $chainType;
                $value = $operator;
                $operator = '=';
            }

            $this->container[] = [$column, $operator, $value, $chainType];
        }
    }

    public function orHaving($column, $operator, $value = null)
    {
        $this->having($column, $operator, $value, 'OR');
    }

    public function havingCount($column, $operator, $value = null, string $chainType = 'AND')
    {
        $column = $this->query->toDbColumn($column);
        $sql = "COUNT($column) $operator ?";
        $this->having(new RawSQL($sql, $value), $chainType);
    }

    public function havingMax($column, $operator, $value = null, string $chainType = 'AND')
    {
        $column = $this->query->toDbColumn($column);
        $sql = "MAX($column) $operator ?";
        $this->having(new RawSQL($sql, $value), $chainType);
    }

    public function havingMin($column, $operator, $value = null, string $chainType = 'AND')
    {
        $column = $this->query->toDbColumn($column);
        $sql = "MIN($column) $operator ?";
        $this->having(new RawSQL($sql, $value), $chainType);
    }

    public function havingAvg($column, $operator, $value = null, string $chainType = 'AND')
    {
        $column = $this->query->toDbColumn($column);
        $sql = "AVG($column) $operator ?";
        $this->having(new RawSQL($sql, $value), $chainType);
    }

    public function havingSum($column, $operator, $value = null, string $chainType = 'AND')
    {
        $column = $this->query->toDbColumn($column);
        $sql = "SUM($column) $operator ?";
        $this->having(new RawSQL($sql, $value), $chainType);
    }

    public function toSql(&$map = []): string
    {
        if (empty($this->container)) {
            return '';
        }

        $ret = '';
        foreach ($this->container as $having) {
            list($column, $operator, $value, $chainType) = $having;
            if (empty($ret)) {
                $ret .= ' HAVING ';
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
