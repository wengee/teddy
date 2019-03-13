<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-06 15:18:23 +0800
 */
namespace SlimExtra\Db;

use SlimExtra\Db\Traits\HasPdoQuery;

class Database implements QueryInterface
{
    use HasPdoQuery;

    const INNER_JOIN = 1;

    const LEFT_JOIN = 2;

    const RIGHT_JOIN = 3;

    const FULL_JOIN = 4;

    const SELECT_SQL = 1;

    const INSERT_SQL = 2;

    const UPDATE_SQL = 3;

    const DELETE_SQL = 4;

    const FETCH_ONE = 1;

    const FETCH_ALL = 2;

    const FETCH_COLUMN = 3;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var string
     */
    protected $default = 'default';

    /**
     * @var array
     */
    protected $connections = [];

    /**
     * @var PDO $instances
     */
    protected $instances = [];

    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->default = array_get($settings, 'default', 'default');
        $this->connections = array_get($settings, 'connections', []);
    }

    public function query(string $sql, array $data = [], array $options = [])
    {
        $options['release'] = true;
        return $this->doQuery($sql, $data, $options);
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }

    public function raw(string $sql): RawSQL
    {
        return new RawSQL($sql);
    }

    protected function getPdo(?string $connection = null, bool $master = true): PDO
    {
        $connection = $connection ?: $this->default;
        if (empty($connection) && !isset($this->connections[$connection])) {
            throw new \InvalidArgumentException("Connection [$connection] doesn't exists.");
        }

        if (!isset($this->instances[$connection])) {
            $this->instances[$connection] = ['master' => null, 'slave' => null];
        }

        $key = $master ? 'master' : 'slave';
        $connectionKey = $connection . '-' . $key;
        if (empty($this->instances[$connectionKey])) {
            $single = false;
            $config = array_get($this->connections, $connection, []);
            if (isset($config['master'])) {
                $config = isset($config[$key]) ? $config[$key] : $config['master'];
            } else {
                $single = true;
            }

            if (isset($config[0]) && !isset($config['host'])) {
                $config = array_random($config);
            }

            $pdo = new PDO($config, [$connection, $key]);
            if ($single) {
                $this->instances[$connection . '-master'] = $pdo;
                $this->instances[$connection . '-slave'] = $pdo;
            } else {
                $this->instances[$connectionKey] = $pdo;
            }
        }
        return $this->instances[$connectionKey];
    }

    protected function release(PDO $pdo, bool $remove = false)
    {
    }
}
