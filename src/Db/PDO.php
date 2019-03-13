<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-02-13 10:36:26 +0800
 */
namespace SlimExtra\Db;

class PDO extends \PDO
{
    /**
     * @var array
     */
    protected $identity = null;

    public function __construct(array $config, $identity = null, bool $persistent = false)
    {
        $this->identity = $identity;

        $engine   = array_get($config, 'engine', 'mysql');
        $host     = array_get($config, 'host', '127.0.0.1');
        $port     = array_get($config, 'port', 3306);
        $name     = array_get($config, 'name', '');
        $user     = array_get($config, 'user', 'root');
        $password = array_get($config, 'password', '');
        $charset  = array_get($config, 'charset', 'utf8mb4');
        $options  = array_get($config, 'options', []);
        $dsn      = $engine . ':host=' . $host . ';port=' . $port . ';dbname=' . $name . ';charset=' . $charset;

        $options = $options + $this->getDefaultOptions();
        parent::__construct($dsn, $user, $password, $options);
    }

    public function getIdentity()
    {
        return $this->identity;
    }

    protected function getDefaultOptions(bool $persistent = false): array
    {
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_CLASS,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
        ];

        if ($persistent) {
            $options[\PDO::ATTR_PERSISTENT] = true;
        }

        return $options;
    }
}
