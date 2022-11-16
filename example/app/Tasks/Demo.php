<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-16 21:57:03 +0800
 */

namespace App\Tasks;

use Illuminate\Support\Str;
use Swoole\Coroutine;
use Teddy\Task;

class Demo extends Task
{
    protected function getUniqueId(): ?string
    {
        return 'demo_'.Str::random(16);
    }

    protected function handle(): void
    {
        echo 'Coroutine ID: '.Coroutine::getCid().PHP_EOL;
        echo 'task handle: '.time().', unique id: '.$this->getUniqueId().PHP_EOL;
        run_task(Foo::class, [], ['delay' => 5]);
    }
}
