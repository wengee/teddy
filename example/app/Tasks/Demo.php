<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 16:09:38 +0800
 */

namespace App\Tasks;

use Teddy\Task;

class Demo extends Task
{
    protected function handle(): void
    {
        echo 'task handle: '.time().', unique id: '.$this->getUniqueId().PHP_EOL;
        run_task(Foo::class, [], ['delay' => 5]);
    }
}
