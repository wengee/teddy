<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-16 22:02:04 +0800
 */

namespace App\Tasks;

use Illuminate\Support\Str;
use Teddy\Task;

class Foo extends Task
{
    protected function getUniqueId(): ?string
    {
        return 'foo_'.Str::random(8);
    }

    protected function handle(): void
    {
        echo 'task handle: '.time().', unique id: '.$this->getUniqueId().PHP_EOL;
    }
}
