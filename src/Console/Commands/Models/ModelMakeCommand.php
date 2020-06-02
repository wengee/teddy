<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-06-02 12:08:34 +0800
 */

namespace Teddy\Console\Commands\Models;

use Illuminate\Support\Str;
use Teddy\Console\Command;
use Teddy\Model\ModelCreator;

class ModelMakeCommand extends Command
{
    protected $description = 'Generate a model file';

    protected $signature = 'make:model {name : The name of the model}
        {--t|table= : The table name}';

    protected function handle(): void
    {
        $name = $this->argument('name');
        $table = $this->option('table');
        $table = $table ?: Str::snake($name);

        $file = pathinfo(make(ModelCreator::class)->create(
            path_join(app()->getBasePath(), 'app', 'Models'),
            $name,
            $table
        ), PATHINFO_FILENAME);

        $this->line("<info>Created Model:</info> {$file}");
    }
}
