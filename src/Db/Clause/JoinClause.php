<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-07 17:05:22 +0800
 */

namespace Teddy\Db\Clause;

use Teddy\Db\Database;
use Teddy\Db\RawSQL;

class JoinClause extends ClauseContainer
{
    public function join($table, $first, $operator = null, $second = null, int $joinType = Database::INNER_JOIN)
    {
        if (!in_array($operator, ['>=', '>', '<=', '<', '=', '!=', '<>'])) {
            $operator = '=';
        }

        $on = [];
        if (is_array($first)) {
            foreach ($first as $c) {
                $c = array_pad((array) $c, 4, null);
                list($x, $y, $z, $t) = $c;
                $x = $this->query->toDbColumn($x);
                $z = $this->query->toDbColumn($z);
                $t = $t ?: 'AND';
                $on[] = [$x, $y, $z, $t];
            }

            $joinType = $operator ?: $joinType;
        } else {
            $first = $this->query->toDbColumn($first);
            $second = $this->query->toDbColumn($second);
            $on = [[$first, $operator, $second, null]];
        }

        $this->container[] = [$table, $on, $joinType];
    }

    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        $this->join($table, $first, $operator, $second, Database::LEFT_JOIN);
    }

    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        $this->join($table, $first, $operator, $second, Database::RIGHT_JOIN);
    }

    public function fullJoin($table, $first, $operator = null, $second = null)
    {
        $this->join($table, $first, $operator, $second, Database::FULL_JOIN);
    }

    public function toSql(&$map = []): string
    {
        if (empty($this->container)) {
            return '';
        }

        $ret = [];
        foreach ($this->container as $join) {
            list($table, $ons, $joinType) = $join;

            $sql = '';
            if ($joinType === Database::FULL_JOIN) {
                $sql .= ' FULL OUTER JOIN ';
            } elseif ($joinType === Database::RIGHT_JOIN) {
                $sql .= ' RIGHT OUTER JOIN ';
            } elseif ($joinType === Database::LEFT_JOIN) {
                $sql .= ' LEFT OUTER JOIN ';
            } else {
                $sql .= ' INNER JOIN ';
            }

            $as = '';
            if (is_array($table) && count($table >= 2)) {
                list($table, $as) = $table;
            }
            $sql .= $this->query->getDbTable($table, $as);

            $tick = false;
            foreach ($ons as $on) {
                list($first, $operator, $second, $chainType) = $on;
                if (!$tick) {
                    $sql .= ' ON ';
                    $tick = true;
                } else {
                    $chainType = $chainType ?: 'AND';
                    $sql .= " $chainType ";
                }

                $sql .= ($first instanceof RawSQL) ? $first->toSql($map, $this->query) : $first;
                if (!empty($second)) {
                    $sql .= " $operator " . (($second instanceof RawSQL) ? $second->toSql($map, $this->query) : $second);
                }
            }

            $ret[] = $sql;
        }

        return implode('', $ret);
    }
}
