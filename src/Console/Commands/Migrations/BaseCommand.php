<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Console\Commands\Migrations;

use Teddy\Console\Command;
use Teddy\Database\Migrations\Migrator;

abstract class BaseCommand extends Command
{
    protected $migrator;

    public function enableCoroutine(): bool
    {
        return true;
    }

    protected function getMigrationPath(): string
    {
        return base_path('migrations');
    }

    protected function getMigrator(): Migrator
    {
        if (!isset($this->migrator)) {
            $this->migrator = make(Migrator::class);
        }

        $this->migrator->setCommand($this);

        return $this->migrator;
    }
}
