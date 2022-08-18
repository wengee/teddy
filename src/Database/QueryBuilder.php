<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-18 17:18:58 +0800
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
use Teddy\Model\Meta;
use Teddy\Model\Model;

/**
 * @method self where(RawSQL|string|string[] $column, mixed $operator = null, mixed $value = null, string $chainType = 'AND')
 * @method self orWhere(RawSQL|string|string[] $column, mixed $operator = null, mixed $value = null)
 * @method self order(string $column, string $direction = 'ASC')
 * @method self orderBy(string $column, string $direction = 'ASC')
 * @method self limit(int $number, int $offset = 0)
 * @method self offset(int $number = 0)
 * @method self group(string ...$columns)
 * @method self groupBy(string ...$columns)
 * @method self join($table, $first, $operator = null, $second = null, int $joinType = SQL::INNER_JOIN)
 * @method self leftJoin($table, $first, $operator = null, $second = null)
 * @method self rightJoin($table, $first, $operator = null, $second = null)
 * @method self fullJoin($table, $first, $operator = null, $second = null)
 * @method self having($column, $operator, $value = null, string $chainType = 'AND')
 * @method self orHaving($column, $operator, $value = null)
 * @method self havingCount($column, $operator, $value = null, string $chainType = 'AND')
 * @method self havingMax($column, $operator, $value = null, string $chainType = 'AND')
 * @method self havingMin($column, $operator, $value = null, string $chainType = 'AND')
 * @method self havingAvg($column, $operator, $value = null, string $chainType = 'AND')
 * @method self havingSum($column, $operator, $value = null, string $chainType = 'AND')
 */
class QueryBuilder
{
    use QuerySelect;
    use QueryInsert;
    use QueryUpdate;
    use QueryDelete;

    /**
     * @var DatabaseInterface
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
     * @var null|WhereClause
     */
    protected $whereClause;

    /**
     * @var null|OrderClause
     */
    protected $orderClause;

    /**
     * @var null|LimitClause
     */
    protected $limitClause;

    /**
     * @var null|GroupClause
     */
    protected $groupClause;

    /**
     * @var null|JoinClause
     */
    protected $joinClause;

    /**
     * @var null|HavingClause
     */
    protected $havingClause;

    /**
     * @var int
     */
    protected $sqlType = 0;

    /**
     * @var null|Meta
     */
    protected $meta;

    /**
     * @var string
     */
    protected $tableSuffix = '';

    /**
     * @var string
     */
    protected $connection;

    /**
     * Constructor.
     */
    public function __construct(DatabaseInterface $db, Model|string $table, ?string $tableSuffix = null)
    {
        $this->db          = $db;
        $this->tableSuffix = $tableSuffix ?: '';
        $this->setTable($table, $tableSuffix);
        $this->transaction = $db instanceof Transaction;
    }

    public function __clone()
    {
        $this->whereClause  = $this->whereClause ? clone $this->whereClause : null;
        $this->orderClause  = $this->orderClause ? clone $this->orderClause : null;
        $this->limitClause  = $this->limitClause ? clone $this->limitClause : null;
        $this->groupClause  = $this->groupClause ? clone $this->groupClause : null;
        $this->joinClause   = $this->joinClause ? clone $this->joinClause : null;
        $this->havingClause = $this->havingClause ? clone $this->havingClause : null;
    }

    public function __call($method, array $args = [])
    {
        $clause = null;

        switch ($method) {
            case 'where':
            case 'orWhere':
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
                throw new \RuntimeException("Tried to call unknown method {$method}");

                break;
        }

        $clause->{$method}(...$args);

        return $this;
    }

    public function __toString()
    {
        return $this->getSql();
    }

    public function as(string $as): self
    {
        $this->as = $as;

        return $this;
    }

    public function connect(string $connection): self
    {
        $this->connection = $connection;

        return $this;
    }

    public function setTable($table, ?string $tableSuffix = null): self
    {
        if (is_subclass_of($table, Model::class)) {
            $this->meta = app('modelManager')->getMeta($table);
        }

        if (!$this->meta) {
            $this->table = strval($table);
        } else {
            $this->table = $this->meta->getTableName($tableSuffix);
        }

        return $this;
    }

    public function getSqlType(): int
    {
        return (int) $this->sqlType;
    }

    public function getSql(array &$map = []): string
    {
        if (SQL::SELECT_SQL === $this->sqlType) {
            return $this->getSelectSql($map);
        }
        if (SQL::INSERT_SQL === $this->sqlType) {
            return $this->getInsertSql($map);
        }
        if (SQL::UPDATE_SQL === $this->sqlType) {
            return $this->getUpdateSql($map);
        }
        if (SQL::DELETE_SQL === $this->sqlType) {
            return $this->getDeleteSql($map);
        }

        return '';
    }

    public function setData(array $data): self
    {
        $data       = $this->toDbColumn($data);
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    public function getDbTable($table = null, ?string $as = null, ?string $tableSuffix = null): string
    {
        if (is_subclass_of($table, Model::class)) {
            $meta = app('modelManager')->getMeta($table);
        }

        $table = $meta ? $meta->getTableName($tableSuffix) : strval($table);
        $table = $this->quote($table);
        $as    = $this->quote($as);

        return empty($as) ? $table : $table.' AS '.$as;
    }

    public function toDbColumn($column)
    {
        if (empty($column) || '*' === $column || ($column instanceof RawSQL)) {
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
        }
        $column = (string) $column;
        if ($this->meta) {
            $column = $this->meta->convertToDbColumn($column);
        }

        return $this->quote($column);
    }

    public function quote($string)
    {
        if (!$string || '*' === $string || ($string instanceof RawSQL)) {
            return $string;
        }

        $string = (string) $string;
        if (false !== strpos($string, '.')) {
            return '`'.str_replace('.', '`.`', $string).'`';
        }

        return '`'.$string.'`';
    }

    protected function getTable(?string $as = null): string
    {
        $as = $as ?: $this->as;

        $table = $this->quote($this->table);
        $as    = $this->quote($as);

        return empty($as) ? $table : $table.' AS '.$as;
    }

    protected function execute(array $options = [])
    {
        $map = [];
        $sql = $this->getSql($map);

        $options['connection']  = $this->connection;
        $options['tableSuffix'] = $this->tableSuffix;
        $options['sqlType']     = $this->sqlType;
        $options['meta']        = $this->meta;

        $readOnly      = $this->sqlType && SQL::SELECT_SQL === $this->sqlType;
        $pdoConnection = $readOnly ?
            $this->db->getReadConnection() :
            $this->db->getWriteConnection();

        try {
            $ret = $pdoConnection->query($sql, $map, $options);
        } catch (Exception $e) {
            $this->releaseConnection($pdoConnection);

            throw $e;
        }

        $this->releaseConnection($pdoConnection);

        return $ret;
    }

    protected function releaseConnection(ConnectionInterface $connection): void
    {
        if (!$this->transaction && ($this->db instanceof Database)) {
            $this->db->releaseConnection($connection);
        }
    }
}
