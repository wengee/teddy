<?php

declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-15 16:59:50 +0800
 */

namespace Teddy\Database\Schema;

use Exception;
use Teddy\Database\PDOConnection;

/**
 * @method static bool hasTable(string $table)
 * @method static array getColumnListing(string $table)
 * @method static void dropAllTables()
 * @method static void dropAllViews()
 * @method static array getAllTables()
 * @method static array getAllViews()
 * @method static bool hasColumn(string $table, string $column)
 * @method static bool hasColumns(string $table, array $columns)
 * @method static void table(string $table, \Closure $callback)
 * @method static void create(string $table, \Closure $callback))
 * @method static void drop(string $table)
 * @method static void dropIfExists(string $table)
 * @method static void rename(string $from, string $to)
 * @method static bool enableForeignKeyConstraints()
 * @method static bool disableForeignKeyConstraints()
 * @method static PDOConnection getConnection()
 * @method static Builder setConnection(PDOConnection $connection)
 * @method static void blueprintResolver(\Closure $resolver)
 */
class Schema
{
    public static function __callStatic($name, $arguments)
    {
        /**
         * @var PDOConnection
         */
        $connection = app('db')->getWriteConnection();

        try {
            $ret = $connection->getSchemaBuilder()->{$name}(...$arguments);
        } catch (Exception $e) {
            app('db')->releaseConnection($connection);

            throw $e;
        }

        app('db')->releaseConnection($connection);

        return $ret;
    }

    public function __call($name, $arguments)
    {
        return self::__callStatic($name, $arguments);
    }
}
