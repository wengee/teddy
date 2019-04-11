<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-11 18:28:00 +0800
 */
namespace Teddy\Db\Traits;

use Exception;
use Illuminate\Support\Str;
use PDO;
use PDOException;
use PDOStatement;
use Teddy\Db\Collection;
use Teddy\Db\Database;
use Teddy\Db\Transaction;

trait HasPdoQuery
{
    public function transaction(callable $callback, ?string $connection = null)
    {
        $firstRun = true;
        $pdo = $this->getPdo($connection ?: $this->default, true);

        RETRY:
        try {
            $pdo->beginTransaction();
            $transaction = new Transaction($pdo);
            $ret = $callback($transaction);
        } catch (PDOException $e) {
            if ($firstRun && $this->canResetPDO($e)) {
                $pdo = $this->release($pdo, true);
                $firstRun = false;
                goto RETRY;
            }

            $pdo->rollBack();
            $this->release($pdo);
            throw $e;
        } catch (Exception $e) {
            $pdo->rollBack();
            $this->release($pdo);
            throw $e;
        }

        if ($ret) {
            $pdo->commit();
        } else {
            $pdo->rollBack();
        }

        $this->release($pdo);
        return true;
    }

    protected function doQuery(string $sql, array $data = [], array $options = [])
    {
        $connection = array_get($options, 'connection');
        $sqlType = array_get($options, 'sqlType');
        $master = $sqlType === Database::SELECT_SQL ? false : true;
        $retryNum = 0;
        $retryTimes = isset($options['retryTimes']) ? intval($options['retryTimes']) : 3;
        $pdo = $this->getPdo($connection, $master);

        RETRY:
        $metaInfo = array_get($options, 'metaInfo');
        $ret = true;
        $error = $stmt = null;
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $this->bindValues($stmt, $data);
            $stmt->execute();
        } catch (PDOException $e) {
            if ($retryNum < $retryTimes && $this->canResetPDO($e)) {
                $pdo = $this->release($pdo, true);
                $retryNum += 1;
                goto RETRY;
            }

            $error = $e;
        } catch (Exception $e) {
            $error = $e;
        }

        if ($sqlType === Database::SELECT_SQL) {
            $fetchType = array_get($options, 'fetchType');
            if ($fetchType === Database::FETCH_ALL) {
                $ret = array_map(function ($data) use ($metaInfo) {
                    return $metaInfo ? $metaInfo->makeInstance($data) : new Collection($data);
                }, $stmt->fetchAll());
            } elseif ($fetchType === Database::FETCH_COLUMN) {
                $ret = $stmt->fetchColumn();
            } else {
                $ret = $stmt->fetch();
                if ($ret && is_array($ret)) {
                    $ret = $metaInfo ? $metaInfo->makeInstance($ret) : new Collection($ret);
                }
            }
        } elseif ($sqlType === Database::INSERT_SQL) {
            if (array_get($options, 'lastInsertId')) {
                $ret = $pdo->lastInsertId();
            }
        } else {
            $ret = $stmt->rowCount();
        }

        if ($stmt) {
            $stmt->closeCursor();
        }

        if (array_get($options, 'release', true)) {
            $this->release($pdo);
        }

        if ($error) {
            throw $error;
        } else {
            return $ret;
        }
    }

    protected function bindValues(PDOStatement $statement, array $bindings)
    {
        foreach ($bindings as $key => $value) {
            $dataType = PDO::PARAM_STR;
            if (is_int($value)) {
                $dataType = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $dataType = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $dataType = PDO::PARAM_NULL;
            }

            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                $dataType
            );
        }
    }

    protected function canResetPDO(PDOException $e)
    {
        $errorInfo = (array) $e->errorInfo;
        if (issest($errorInfo[1]) && ($errorInfo[1] === 1461 || $errorInfo[1] === 2006)) {
            return true;
        }

        $message = $e->getMessage();
        return Str::contains($message, [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'SSL connection has been closed unexpectedly',
        ]);
    }
}
