<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:53 +0800
 */

namespace App\Tasks;

use Teddy\Task;

class Demo extends Task
{
    protected $exclusive = false;

    protected function handle()
    {
        // echo 'task handle' . PHP_EOL;
        // sleep(1);
        return 'result ok';
    }
}
