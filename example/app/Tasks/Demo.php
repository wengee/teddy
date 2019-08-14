<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 18:46:30 +0800
 */
namespace App\Tasks;

use Teddy\Task;

class Demo extends Task
{
    protected function handle()
    {
        echo 'task start' . PHP_EOL;
        sleep(1);
        echo date('Y-m-d H:i:s') . PHP_EOL;
        echo 'task end' . PHP_EOL;
    }

    public function finish()
    {
        echo 'demo finish' . PHP_EOL;
        return 'abc';
    }
}
