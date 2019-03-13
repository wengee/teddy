<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 15:30:24 +0800
 */
namespace SlimExtra\Db;

use SlimExtra\Db\Clause\GroupClause;
use SlimExtra\Db\Clause\HavingClause;
use SlimExtra\Db\Clause\JoinClause;
use SlimExtra\Db\Clause\LimitClause;
use SlimExtra\Db\Clause\OrderClause;
use SlimExtra\Db\Clause\WhereClause;
use SlimExtra\Db\Traits\QueryDelete;
use SlimExtra\Db\Traits\QueryInsert;
use SlimExtra\Db\Traits\QuerySelect;
use SlimExtra\Db\Traits\QueryUpdate;

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
     * @param QueryInterface $db
     * @param string|object  $table
     */
    public function __construct(QueryInterface $db, $table)
    {
        $this->db = $db;
        $this->setTable($table);
    }

    public function __clone()
    {
        $this->whereClause = clone $this->whereClause;
        $this->orderClause = clone $this->orderClause;
        $this->limitClause = clone $this->limitClause;
        $this->groupClause = clone $this->groupClause;
        $this->joinClause = clone $this->joinClause;
        $this->havingClause = clone $this->havingClause;
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

    public function getSql(array &$map = []): string
    {
        if ($this->sqlType === Database::SELECT_SQL) {
            return $this->getSelectSql($map);
        } elseif ($this->sqlType === Database::INSERT_SQL) {
            return $this->getInsertSql($map);
        } elseif ($this->sqlType === Database::UPDATE_SQL) {
            return $this->getUpdateSql($map);
        } elseif ($this->sqlType === Database::DELETE_SQL) {
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

        return $this->db->query($sql, $map, $options);
    }

    public function __call($method, array $args = [])
    {
        $clause = null;
        switch ($method) {
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
