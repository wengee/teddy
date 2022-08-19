<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-19 11:26:01 +0800
 */

namespace Teddy\Database;

interface DatabaseInterface
{
    public function table(string $table): QueryBuilder;

    public function raw(string $sql): RawSQL;

    public function getReadConnection(): DbConnectionInterface;

    public function getWriteConnection(): DbConnectionInterface;
}
