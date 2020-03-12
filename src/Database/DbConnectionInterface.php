<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-11 16:57:30 +0800
 */

namespace Teddy\Database;

use Teddy\Database\Schema\Builder;
use Teddy\Interfaces\ConnectionInterface;

interface DbConnectionInterface extends ConnectionInterface
{
    public function getConfig(string $key);

    public function getTablePrefix(): string;

    public function getDatabaseName(): string;

    public function beginTransaction(): void;

    public function rollBack(): void;

    public function commit(): void;

    public function query(string $sql, array $data = [], array $options = []);

    public function select(string $sql, array $data = []);

    public function getSchemaBuilder(): Builder;

    public function getSchemaGrammar(): Grammar;
}
