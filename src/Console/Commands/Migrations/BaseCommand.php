<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-11 23:50:14 +0800
 */

namespace Teddy\Console\Commands\Migrations;

use Teddy\Abstracts\AbstractCommand;
use Teddy\Database\Migrations\Migrator;

abstract class BaseCommand extends AbstractCommand
{
    protected $enableCoroutine = true;

    /**
     * @var null|Migrator
     */
    protected $migrator;

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
