<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-02-27 11:29:26 +0800
 */
namespace Teddy\Db;

use Teddy\Db\Traits\HasPdoQuery;

class Transaction implements QueryInterface
{
    use HasPdoQuery;

    /**
     * @var PDO
     */
    protected $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function query(string $sql, array $data = [], array $options = [])
    {
        $options['release'] = false;
        $options['retryTimes'] = 0;
        return $this->doQuery($sql, $data, $options);
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
        return $query;
    }

    protected function getPdo(): PDO
    {
        return $this->pdo;
    }
}
