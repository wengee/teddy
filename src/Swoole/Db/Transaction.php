<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-18 17:16:40 +0800
 */
namespace SlimExtra\Swoole\Db;

use SlimExtra\Db\QueryBuilder;
use SlimExtra\Db\QueryInterface;

class Transaction implements QueryInterface
{
    use QueryTrait;

    /**
     * @var MySQL
     */
    protected $client;

    public function __construct(MySQL $client)
    {
        $this->client = $client;
    }

    public function query(string $sql, array $data = [], array $options = [])
    {
        $options['release'] = false;
        return $this->doQuery($sql, $data, $options);
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
        return $query;
    }

    protected function getClient(): MySQL
    {
        return $this->client;
    }
}
