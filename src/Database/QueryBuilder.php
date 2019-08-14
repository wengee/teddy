<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 14:39:11 +0800
 */

namespace Teddy\Database;

use Exception;
use Teddy\Database\Clause\GroupClause;
use Teddy\Database\Clause\HavingClause;
use Teddy\Database\Clause\JoinClause;
use Teddy\Database\Clause\LimitClause;
use Teddy\Database\Clause\OrderClause;
use Teddy\Database\Clause\WhereClause;
use Teddy\Database\Traits\QueryDelete;
use Teddy\Database\Traits\QueryInsert;
use Teddy\Database\Traits\QuerySelect;
use Teddy\Database\Traits\QueryUpdate;
use Teddy\Interfaces\ConnectionInterface;

class QueryBuilder
{
    use QuerySelect, QueryInsert, QueryUpdate, QueryDelete;

    /**
     * @var Database
     */
    protected $db;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var bool
     */
    protected $transaction = false;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var string
     */
    protected $as;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var WhereClause
     */
    protected $whereClause;

    /**
     * @var OrderClause
     */
    protected $orderClause;

    /**
     * @var LimitClause
     */
    protected $limitClause;

    /**
     * @var GroupClause
     */
    protected $groupClause;

    /**
     * @var JoinClause
     */
    protected $joinClause;

    /**
     * @var HavingClause
     */
    protected $havingClause;

    /**
     * @var int
     */
    protected $sqlType = 0;

    /**
     * @var string
     */
    protected $metaInfo;

    /**
     * @var string
     */
    protected $connection;

    /**
     * Constructor.
     *
     * @param DbConnectionInterface $db
     * @param string|object  $table
     */
    public function __construct(DbConnectionInterface $db, $table)
    {
        $this->db = $db;
        $this->setTable($table);
        $this->transaction = $db instanceof Transaction;
    }

    public function __clone()
    {
        $this->whereClause = $this->whereClause ? clone $this->whereClause : null;
        $this->orderClause = $this->orderClause ? clone $this->orderClause : null;
        $this->limitClause = $this->limitClause ? clone $this->limitClause : null;
        $this->groupClause = $this->groupClause ? clone $this->groupClause : null;
        $this->joinClause = $this->joinClause ? clone $this->joinClause : null;
        $this->havingClause = $this->havingClause ? clone $this->havingClause : null;
    }

    public function as(string $as)
    {
        $this->as = $as;
        return $this;
    }

    public function connect(string $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    public function setTable($table)
    {
        $this->metaInfo = app('modelManager')->metaInfo($table);
        if (!$this->metaInfo) {
            $this->table = strval($table);
        } else {
            $this->table = $this->metaInfo->tableName();
        }

        return $this;
    }

    public function getSqlType(): int
    {
        return (int) $this->sqlType;
    }

    public function getSql(array &$map = []): string
    {
        if ($this->sqlType === SQL::SELECT_SQL) {
            return $this->getSelectSql($map);
        } elseif ($this->sqlType === SQL::INSERT_SQL) {
            return $this->getInsertSql($map);
        } elseif ($this->sqlType === SQL::UPDATE_SQL) {
            return $this->getUpdateSql($map);
        } elseif ($this->sqlType === SQL::DELETE_SQL) {
            return $this->getDeleteSql($map);
        }

        return '';
    }

    public function setData(array $data)
    {
        $data = $this->toDbColumn($data);
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function getDbTable($table = null, ?string $as = null): string
    {
        $metaInfo = app('modelManager')->metaInfo($table);
        $table = $metaInfo ? $metaInfo->tableName() : strval($table);

        return empty($as) ? $table : $table . ' AS ' . $as;
    }

    public function toDbColumn($column)
    {
        if (empty($this->metaInfo) || empty($column) || $column === '*' || ($column instanceof RawSQL)) {
            return $column;
        }

        if (is_array($column)) {
            $ret = [];
            foreach ($column as $k => $v) {
                if (is_int($k)) {
                    $ret[] = $this->toDbColumn($v);
                } else {
                    $ret[$this->toDbColumn($k)] = $v;
                }
            }

            return $ret;
        } else {
            $column = (string) $column;
            return $this->metaInfo->transformKey($column);
        }
    }

    protected function getTable(?string $as = null): string
    {
        $as = $as ?: $this->as;
        return empty($as) ? $this->table : $this->table . ' AS ' . $as;
    }

    protected function execute(array $options = [])
    {
        $map = [];
        $sql = $this->getSql($map);
        $options['connection'] = $this->connection;
        $options['sqlType'] = $this->sqlType;
        $options['metaInfo'] = $this->metaInfo;

        $readOnly = $this->sqlType && $this->sqlType === SQL::SELECT_SQL;
        $pdoConnection = $readOnly ?
            $this->db->getReadConnection() :
            $this->db->getWriteConnecction();

        try {
            $ret = $pdoConnection->query($sql, $map, $options);
        } catch (Exception $e) {
            $this->release($pdoConnection);
            throw $e;
        }

        $this->release($pdoConnection);
        return $ret;
    }

    protected function release(ConnectionInterface $connection)
    {
        if (!$this->transaction) {
            $this->db->release($connection);
        }
    }

    public function __call($method, array $args = [])
    {
        $clause = null;
        switch ($method) {
            case 'search':
            case 'orSearch':
            case 'where':
            case 'orWhere':
            case 'whereBetween':
            case 'orWhereBetween':
            case 'whereNotBetween':
            case 'orWhereNotBetween':
            case 'whereIn':
            case 'orWhereIn':
            case 'whereNotIn':
            case 'orWhereNotIn':
            case 'whereLike':
            case 'orWhereLike':
            case 'whereNotLike':
            case 'orWhereNotLike':
            case 'whereNull':
            case 'orWhereNull':
            case 'whereNotNull':
            case 'orWhereNotNull':
                if (!isset($this->whereClause)) {
                    $this->whereClause = new WhereClause($this);
                }

                $clause = $this->whereClause;
                break;

            case 'order':
            case 'orderBy':
                if (!isset($this->orderClause)) {
                    $this->orderClause = new OrderClause($this);
                }

                $clause = $this->orderClause;
                break;

            case 'limit':
            case 'offset':
                if (!isset($this->limitClause)) {
                    $this->limitClause = new LimitClause($this);
                }

                $clause = $this->limitClause;
                break;

            case 'group':
            case 'groupBy':
                if (!isset($this->groupClause)) {
                    $this->groupClause = new GroupClause($this);
                }

                $clause = $this->groupClause;
                break;

            case 'join':
            case 'leftJoin':
            case 'rightJoin':
            case 'fullJoin':
                if (!isset($this->joinClause)) {
                    $this->joinClause = new JoinClause($this);
                }

                $clause = $this->joinClause;
                break;

            case 'having':
            case 'orHaving':
            case 'havingCount':
            case 'havingMax':
            case 'havingMin':
            case 'havingAvg':
            case 'havingSum':
                if (!isset($this->havingClause)) {
                    $this->havingClause = new HavingClause($this);
                }

                $clause = $this->havingClause;
                break;

            default:
                throw new \RuntimeException("Tried to call unknown method $method");
                break;
        }

        $clause->{$method}(...$args);
        return $this;
    }

    public function __toString()
    {
        return $this->getSql();
    }
}
