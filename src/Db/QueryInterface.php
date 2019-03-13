<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-09 17:28:50 +0800
 */
namespace SlimExtra\Db;

interface QueryInterface
{
    public function query(string $sql, array $data = [], array $options = []);
}
