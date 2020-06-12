<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-06-12 11:19:41 +0800
 */

namespace Teddy\Database\Migrations;

use Illuminate\Support\Collection;
use Teddy\Database\Schema\Blueprint;

class MigrationRepository
{
    public const TABLE_NAME = 'migrations';

    protected $connection;

    public function __construct()
    {
        $this->connection = app('db')->getWriteConnection();
    }

    public function getRan()
    {
        $data = $this->table()
            ->orderBy('batch', 'ASC')
            ->orderBy('migration', 'ASC')
            ->all();

        return Collection::make($data)->pluck('migration')->all();
    }

    public function getMigrationBatches()
    {
        $data = $this->table()
                ->orderBy('batch', 'ASC')
                ->orderBy('migration', 'ASC')
                ->all();

        return Collection::make($data)->pluck('batch', 'migration')->all();
    }

    public function getLast()
    {
        $query = $this->table()->where('batch', $this->getLastBatchNumber());
        return $query->orderBy('migration', 'desc')->all();
    }

    public function log(string $file, int $batch): void
    {
        $record = ['migration' => $file, 'batch' => $batch];
        $this->table()->insert($record);
    }

    public function delete(string $migration): void
    {
        $this->table()->where('migration', $migration)->delete();
    }

    public function getNextBatchNumber(): int
    {
        return $this->getLastBatchNumber() + 1;
    }

    public function getLastBatchNumber(): int
    {
        return (int) $this->table()->max('batch');
    }

    public function createRepository(): void
    {
        $schema = $this->connection->getSchemaBuilder();
        $schema->create(self::TABLE_NAME, function (Blueprint $table): void {
            $table->increments('id');
            $table->string('migration', 128);
            $table->integer('batch');

            $table->unique('migration');
            $table->index('batch');
        });
    }

    public function repositoryExists()
    {
        $schema = $this->connection->getSchemaBuilder();
        return $schema->hasTable(self::TABLE_NAME);
    }

    protected function table()
    {
        return app('db')->table(self::TABLE_NAME);
    }
}
