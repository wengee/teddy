<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 20:52:52 +0800
 */
namespace App\Tasks;

use Teddy\Task;

class Demo extends Task
{
    protected function handle()
    {
        echo 'task handle' . PHP_EOL;
        return 'result ok';
    }
}
