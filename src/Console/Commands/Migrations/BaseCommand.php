<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:59:57 +0800
 */

namespace Teddy\Console\Commands\Migrations;

use Teddy\Abstracts\AbstractCommand;
use Teddy\Database\Migrations\Migrator;

abstract class BaseCommand extends AbstractCommand
{
    protected bool $enableCoroutine = true;

    protected ?Migrator $migrator = null;

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
