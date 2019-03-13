<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-06 15:23:20 +0800
 */
namespace SlimExtra\Swoole\Db;

use SlimExtra\Db\Collection;
use SlimExtra\Db\Database as SyncDatabase;

trait QueryTrait
{
    protected function doQuery(string $sql, array $data = [], array $options = [])
    {
        $connection = array_get($options, 'connection');
        $sqlType = array_get($options, 'sqlType');
        $master = $sqlType === SyncDatabase::SELECT_SQL ? false : true;
        $client = $this->getClient($connection, $master);

        RETRY:
        $metaInfo = array_get($options, 'metaInfo');
        $ret = true;
        $error = null;

        $stmt = $client->prepare($sql);
        if (!$stmt->execute($data)) {
            $errno = $client->errno;
            if ($errno === 1461 || $errno === 2006) {
                $client = $this->release($client, true);
                goto RETRY;
            }

            $error = new DbException($client->error, $errno);
        } else {
            if ($sqlType === SyncDatabase::SELECT_SQL) {
                $fetchType = array_get($options, 'fetchType');
                if ($fetchType === SyncDatabase::FETCH_ALL) {
                    $ret = array_map(function ($data) use ($metaInfo) {
                        return $metaInfo ? $metaInfo->makeInstance($data) : new Collection($data);
                    }, $stmt->fetchAll());
                } else {
                    $ret = $stmt->fetch();
                    if ($ret) {
                        if ($fetchType === SyncDatabase::FETCH_COLUMN) {
                            $ret = $ret ? array_values($ret) : [];
                            $ret = isset($ret[0]) ? $ret[0] : null;
                        } else {
                            $ret = $metaInfo->makeInstance($ret);
                        }
                    }
                }
            } elseif ($sqlType === SyncDatabase::INSERT_SQL) {
                if (array_get($options, 'lastInsertId')) {
                    $ret = $client->insert_id;
                }
            } else {
                $ret = $client->affected_rows;
            }
        }

        if (array_get($options, 'release', true)) {
            $this->release($client);
        }

        if ($error) {
            throw $error;
        } else {
            return $ret;
        }
    }
}
