<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Database\Clause;

use Teddy\Database\RawSQL;

class WhereClause extends ClauseContainer
{
    public static $operators = [
        '>='            => '>=',
        '>'             => '>',
        '<='            => '<=',
        '<'             => '<',
        '='             => '=',
        '!='            => '!=',
        '<>'            => 'BETWEEN',
        '><'            => 'NOT BETWEEN',
        '~'             => 'LIKE',
        '!~'            => 'NOT LIKE',
        '^'             => 'REGEXP',
        '%'             => 'MATCH',

        'IN'            => 'IN',
        'NOT IN'        => 'NOT IN',
        'BETWEEN'       => 'BETWEEN',
        'NOT BETWEEN'   => 'NOT BETWEEN',
        'LIKE'          => 'LIKE',
        'NOT LIKE'      => 'NOT LIKE',
        'REGEXP'        => 'REGEXP',
        'MATCH'         => 'MATCH',
    ];

    /**
     * @param RawSQL|string|string[] $column
     * @param mixed                  $operator
     * @param mixed                  $value
     */
    public function where($column, $operator = null, $value = null, string $chainType = 'AND'): void
    {
        $this->_where($column, $operator, $value, $chainType, $this->container);
    }

    /**
     * @param RawSQL|string|string[] $column
     * @param mixed                  $operator
     * @param mixed                  $value
     */
    public function orWhere($column, $operator = null, $value = null): void
    {
        $this->where($column, $operator, $value, 'OR');
    }

    public function toSql(&$map = []): string
    {
        if (empty($this->container)) {
            return '';
        }

        return ' WHERE '.$this->_toSql($this->container, $map);
    }

    protected function _toSql(array $container, &$map = []): string
    {
        $ret = '';
        foreach ($container as $where) {
            [$column, $operator, $value, $chainType] = $where;
            if (!$column) {
                continue;
            }
            if ($ret) {
                $ret .= $chainType ? " {$chainType} " : ' AND ';
            }

            if (is_array($column)) {
                $ret .= '('.$this->_toSql($column, $map).')';
            } elseif ($column instanceof RawSQL) {
                $ret .= '('.$column->toSql($map, $this->query).')';
            } else {
                $ret .= "({$column} {$operator} ?)";
                $map[] = $value;
            }
        }

        return $ret;
    }

    /**
     * @param RawSQL|string|string[] $column
     * @param mixed                  $operator
     * @param mixed                  $value
     */
    protected function _where($column, $operator = null, $value = null, string $chainType = 'AND', array &$container = []): void
    {
        if (is_array($column) && 'MATCH' !== $operator && '%' !== $operator) {
            $subContainer = [];
            $subChainType = $operator ?: 'AND';
            foreach ($column as $c) {
                $subColumn   = $c[0] ?? null;
                $subOperator = $c[1] ?? null;
                $subValue    = $c[2] ?? null;

                $this->_where($subColumn, $subOperator, $subValue, $subChainType, $subContainer);
            }

            $container[] = [$subContainer, null, null, $chainType];
        } elseif ($column instanceof RawSQL) {
            $chainType   = $operator ?: $chainType;
            $container[] = [$column, null, null, $chainType];
        } else {
            $column = $this->query->toDbColumn($column);
            if (is_string($operator) && isset(self::$operators[$operator])) {
                $operator = self::$operators[$operator];
            } else {
                $chainType = $value ?: $chainType;
                $value     = $operator;
                $operator  = '=';
            }

            if (null === $value) {
                $column = new RawSQL($column.' IS'.('!=' === $operator ? ' NOT' : '').' NULL');
            } elseif ('BETWEEN' === $operator || 'NOT BETWEEN' === $operator) {
                $value  = array_values((array) $value);
                $column = new RawSQL("{$column} {$operator} ? AND ?", ...$value);
            } elseif ('MATCH' === $operator) {
                $mode = 'boolean';
                if (is_array($value)) {
                    $mode  = $value[1] ?? '';
                    $value = $value[0] ?? strval($value);
                }

                $modeSQL = '';

                switch ($mode) {
                    case 'query':
                        $modeSQL = ' WITH QUERY EXPANSION';

                        break;

                    case 'boolean':
                        $modeSQL = ' IN BOOLEAN MODE';

                        break;

                    case 'natural+query':
                        $modeSQL = ' IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION';

                        break;

                    case 'natural':
                        $modeSQL = ' IN NATURAL LANGUAGE MODE';

                        break;
                }

                $column = is_array($column) ? implode(', ', $column) : $column;
                $sql    = "MATCH({$column}) AGAINST(?{$modeSQL})";
                $column = new RawSQL($sql, $value);
            } elseif (is_array($value)) {
                if ('!=' === $operator) {
                    $operator = 'NOT IN';
                } elseif ('IN' !== $operator && 'NOT IN' !== $operator) {
                    $operator = 'IN';
                }

                $value      = array_values($value);
                $placements = implode(', ', array_fill(0, count($value), '?'));
                $sql        = "{$column} {$operator} ({$placements})";
                $column     = new RawSQL($sql, ...$value);
            }

            $container[] = [$column, $operator, $value, $chainType];
        }
    }
}
