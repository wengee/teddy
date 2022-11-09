<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-09 16:08:35 +0800
 */

namespace App\Tasks;

use Teddy\Task;

class Foo extends Task
{
    protected function handle(): void
    {
        echo 'task handle: '.time().', unique id: '.$this->getUniqueId().PHP_EOL;
    }
}
