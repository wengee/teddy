<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-18 15:32:04 +0800
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

        'BETWEEN'       => 'BETWEEN',
        'NOT BETWEEN'   => 'NOT BETWEEN',
        'LIKE'          => 'LIKE',
        'NOT LIKE'      => 'NOT LIKE',
        'REGEXP'        => 'REGEXP',
    ];

    public function search($match, string $against, int $mode = 3, string $chainType = 'AND'): void
    {
        $match = is_array($match) ? $match : [$match];
        $match = array_map(function ($c) {
            return $this->query->toDbColumn($c);
        }, $match);
        $match = implode(', ', $match);

        $modeSQL = '';
        switch ($mode) {
            case 4:
                $modeSQL = ' WITH QUERY EXPANSION';
                break;

            case 3:
                $modeSQL = ' IN BOOLEAN MODE';
                break;

            case 2:
                $modeSQL = ' IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION';
                break;

            default:
                $modeSQL = ' IN NATURAL LANGUAGE MODE';
        }
        $sql = "MATCH({$match}) AGAINST(?{$modeSQL})";
        $column = new RawSQL($sql, $against);
        $this->where($column, null, null, $chainType);
    }

    public function orSearch($match, string $against, int $mode = 3): void
    {
        $this->search($match, $against, $booleanMode, 'OR');
    }

    public function where($column, $operator = null, $value = null, string $chainType = 'AND'): void
    {
        $this->_where($column, $operator, $value, $chainType, $this->container);
    }

    public function orWhere($column, $operator = null, $value = null): void
    {
        $this->where($column, $operator, $value, 'OR');
    }

    public function whereBetween($column, array $values, string $chainType = 'AND', bool $not = false): void
    {
        if (count($values) === 2) {
            $this->where($column, $not ? '><' : '<>', $values, $chainType);
        }
    }

    public function orWhereBetween($column, array $values): void
    {
        $this->whereBetween($column, $values, 'OR', false);
    }

    public function whereNotBetween($column, array $values, string $chainType = 'AND'): void
    {
        $this->whereBetween($column, $values, $chainType, true);
    }

    public function orWhereNotBetween($column, array $values): void
    {
        $this->whereBetween($column, $values, 'OR', true);
    }

    public function whereIn($column, array $values, string $chainType = 'AND'): void
    {
        if (!empty($values)) {
            $this->where($column, $not ? '!=' : '=', $values, $chainType);
        }
    }

    public function orWhereIn($column, array $values): void
    {
        $this->whereIn($column, $values, 'OR', false);
    }

    public function whereNotIn($column, array $values, string $chainType = 'AND'): void
    {
        $this->whereIn($column, $values, $chainType, true);
    }

    public function orWhereNotIn($column, array $values): void
    {
        $this->whereIn($column, $values, 'OR', true);
    }

    public function whereLike($column, string $value, string $chainType = 'AND', bool $not = false): void
    {
        $this->where($column, $not ? '!~' : '~', $value, $chainType);
    }

    public function orWhereLike($column, string $value): void
    {
        $this->whereLike($column, $value, 'OR', false);
    }

    public function whereNotLike($column, string $value, string $chainType = 'AND'): void
    {
        $this->whereLike($column, $value, $chainType, true);
    }

    public function orWhereNotLike($column, string $value): void
    {
        $this->whereLike($column, $value, 'OR', true);
    }

    public function whereNull($column, string $chainType = 'AND', bool $not = false): void
    {
        $this->where($column, $not ? '!=' : '=', null, $chainType);
    }

    public function orWhereNull($column): void
    {
        $this->whereNull($column, 'OR', false);
    }

    public function whereNotNull($column, string $chainType = 'AND'): void
    {
        $this->whereNull($column, $chainType, true);
    }

    public function orWhereNotNull($column): void
    {
        $this->whereNull($column, 'OR', true);
    }

    public function toSql(&$map = []): string
    {
        if (empty($this->container)) {
            return '';
        }

        return ' WHERE ' . $this->_toSql($this->container, $map);
    }

    protected function _toSql(array $container, &$map = []): string
    {
        $ret = '';
        foreach ($container as $where) {
            list($column, $operator, $value, $chainType) = $where;
            if (!$column) {
                continue;
            } elseif ($ret) {
                $ret .= $chainType ? " {$chainType} " : ' AND ';
            }

            if (is_array($column)) {
                $ret .= '(' . $this->_toSql($column, $map) . ')';
            } elseif ($column instanceof RawSQL) {
                $ret .= $column->toSql($map, $this->query);
            } else {
                $ret .= "{$column} {$operator} ?";
                $map[] = $value;
            }
        }

        return $ret;
    }

    protected function _where($column, $operator = null, $value = null, string $chainType = 'AND', array &$container = []): void
    {
        if ($column instanceof RawSQL) {
            $chainType = $operator ?: $chainType;
            $container[] = [$column, null, null, $chainType];
        } elseif (is_array($column)) {
            $subContainer = [];
            $subChainType = $operator ?: 'AND';
            foreach ($column as $c) {
                $subColumn = $c[0] ?? null;
                $subOperator = $c[1] ?? null;
                $subValue = $c[2] ?? null;

                $this->_where($subColumn, $subOperator, $subValue, $subChainType, $subContainer);
            }

            $container[] = [$subContainer, null, null, $chainType];
        } else {
            $column = $this->query->toDbColumn($column);
            if (isset(self::$operators[$operator])) {
                $operator = self::$operators[$operator];
            } else {
                $chainType = $value ?: $chainType;
                $value = $operator;
                $operator = '=';
            }

            if ($value === null) {
                $column = new RawSQL($column . ' IS' . ($operator === '!=' ? ' NOT' : '') . ' NULL');
            } elseif ($operator === 'BETWEEN' || $operator === 'NOT BETWEEN') {
                if (is_array($value) && count($value) === 2) {
                    $value = array_values($value);
                    $column = new RawSQL("{$column} {$operator} ? AND ?", ...$value);
                } else {
                    return;
                }
            } elseif (is_array($value)) {
                $sql = $column . ($operator === '!=' ? ' NOT' : '') . ' IN (' . implode(', ', array_fill(0, count($value), '?')) . ')';
                $value = array_values($value);
                $column = new RawSQL($sql, ...$value);
            }

            $container[] = [$column, $operator, $value, $chainType];
        }
    }
}
