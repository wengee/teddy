<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-17 20:52:51 +0800
 */

namespace Teddy\Database\Clause;

use Teddy\Database\RawSQL;
use Teddy\Database\SQL;

class JoinClause extends ClauseContainer
{
    public function join($table, $first, $operator = null, $second = null, int $joinType = SQL::INNER_JOIN): void
    {
        if (!in_array($operator, ['>=', '>', '<=', '<', '=', '!=', '<>'], true)) {
            $operator = '=';
        }

        $on = [];
        if (is_array($first)) {
            foreach ($first as $c) {
                $c               = array_pad((array) $c, 4, null);
                [$x, $y, $z, $t] = $c;
                $x               = $this->query->toDbColumn($x);
                $z               = $this->query->toDbColumn($z);
                $t               = $t ?: 'AND';
                $on[]            = [$x, $y, $z, $t];
            }

            $joinType = $operator ?: $joinType;
        } else {
            $first  = $this->query->toDbColumn($first);
            $second = $this->query->toDbColumn($second);
            $on     = [[$first, $operator, $second, null]];
        }

        $this->container[] = [$table, $on, $joinType];
    }

    public function leftJoin($table, $first, $operator = null, $second = null): void
    {
        $this->join($table, $first, $operator, $second, SQL::LEFT_JOIN);
    }

    public function rightJoin($table, $first, $operator = null, $second = null): void
    {
        $this->join($table, $first, $operator, $second, SQL::RIGHT_JOIN);
    }

    public function fullJoin($table, $first, $operator = null, $second = null): void
    {
        $this->join($table, $first, $operator, $second, SQL::FULL_JOIN);
    }

    public function toSql(&$map = []): string
    {
        if (empty($this->container)) {
            return '';
        }

        $ret = [];
        foreach ($this->container as $join) {
            [$table, $ons, $joinType] = $join;

            $sql = '';
            if (SQL::FULL_JOIN === $joinType) {
                $sql .= ' FULL OUTER JOIN ';
            } elseif (SQL::RIGHT_JOIN === $joinType) {
                $sql .= ' RIGHT OUTER JOIN ';
            } elseif (SQL::LEFT_JOIN === $joinType) {
                $sql .= ' LEFT OUTER JOIN ';
            } else {
                $sql .= ' INNER JOIN ';
            }

            $as     = null;
            $suffix = null;
            if (is_array($table)) {
                $suffix = $table[2] ?? null;
                $as     = $table[1] ?? null;
                $table  = $table[0] ?? null;
            }

            $sql .= $this->query->getDbTable($table, $as, $suffix);

            $tick = false;
            foreach ($ons as $on) {
                [$first, $operator, $second, $chainType] = $on;
                if (!$tick) {
                    $sql .= ' ON ';
                    $tick = true;
                } else {
                    $chainType = $chainType ?: 'AND';
                    $sql .= " {$chainType} ";
                }

                $sql .= ($first instanceof RawSQL) ? $first->toSql($map, $this->query) : $first;
                if (!empty($second)) {
                    $sql .= " {$operator} ".(($second instanceof RawSQL) ? $second->toSql($map, $this->query) : $second);
                }
            }

            $ret[] = $sql;
        }

        return implode('', $ret);
    }
}
