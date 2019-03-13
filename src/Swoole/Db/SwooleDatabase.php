<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-06 15:23:37 +0800
 */
namespace Teddy\Swoole\Db;

use Teddy\Db\QueryBuilder;
use Teddy\Db\QueryInterface;
use Teddy\Db\RawSQL;
use Teddy\Swoole\ConnectionPool;

class SwooleDatabase implements QueryInterface
{
    use QueryTrait;

    /**
     * @var array
     */
    protected $pool = [];

    protected $settings = [];

    /**
     * @var string
     */
    protected $default = 'default';

    /**
     * @var array
     */
    protected $connections = [];

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

    public function transaction(callable $callback, ?string $connection = null)
    {
        $client = $this->getClient($connection ?: $this->default, true);

        RETRY:
        $error = null;
        try {
            $this->querySql($client, 'SET AUTOCOMMIT=0');
            $this->querySql($client, 'BEGIN');

            $transaction = new Transaction($client);
            if ($callback($transaction) === false) {
                throw new DbException('Transaction is failed.');
            }
        } catch (\Exception $e) {
            $errno = $e->getCode();
            if ($errno === 1461 || $errno === 2006) {
                $client = $this->release($client, true);
                goto RETRY;
            } else {
                $this->querySql($client, 'ROLLBACK', true);
                throw $e;
            }
        } finally {
            $this->querySql($client, 'COMMIT', true);
            $this->querySql($client, 'SET AUTOCOMMIT=1', true);
        }

        $this->release($client);
        return true;
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }

    public function raw(string $sql): RawSQL
    {
        return new RawSQL($sql);
    }

    protected function querySql(MySQL $client, string $sql, bool $quiet = false): bool
    {
        if (!@$client->query($sql)) {
            if ($quiet) {
                return false;
            } else {
                throw new DbException($client->error, $client->errno);
            }
        }

        return true;
    }

    protected function getClient(?string $connection = null, bool $master = true): MySQL
    {
        $connection = $connection ?: $this->default;
        if (empty($connection) && !isset($this->connections[$connection])) {
            throw new \InvalidArgumentException("Connection [$connection] doesn't exists.");
        }

        if (!isset($this->pool[$connection])) {
            $this->pool[$connection] = ['master' => null, 'slave' => null];
        }

        $key = $master ? 'master' : 'slave';
        $connectionKey = $connection . '-' . $key;
        if (empty($this->pool[$connectionKey])) {
            $poolOptions = array_get($this->settings, 'pool', []);

            $single = false;
            $config = array_get($this->connections, $connection, []);
            if (isset($config['master'])) {
                $config = isset($config[$key]) ? $config[$key] : $config['master'];
            } else {
                $single = true;
            }

            $pool = new ConnectionPool($poolOptions, function () use ($config, $connectionKey) {
                if (isset($config[0]) && !isset($config['host'])) {
                    $config = array_random($config);
                }

                $engine = array_get($config, 'engine', 'mysql');
                if ($engine !== 'mysql') {
                    throw new DbException('The database engine is not supported.');
                }

                $client = new MySQL($connectionKey);
                if (!$client->connect($config)) {
                    throw new DbException('Cannot connect to mysql.');
                }

                return $client;
            });

            if ($single) {
                $this->pool[$connection . '-master'] = $pool;
                $this->pool[$connection . '-slave'] = $pool;
            } else {
                $this->pool[$connectionKey] = $pool;
            }
        }

        return $this->pool[$connectionKey]->get();
    }

    protected function release(MySQL $client, bool $renew = false)
    {
        $connectionKey = $client->getIdentity() ?: [null, null];
        if ($connectionKey && isset($this->pool[$connectionKey])) {
            if ($renew) {
                return $this->pool[$connectionKey]->renew($client);
            } else {
                $this->pool[$connectionKey]->put($client);
            }
        }

        return true;
    }
}
