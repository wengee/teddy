<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-22 11:16:34 +0800
 */

namespace App\Tasks;

use Teddy\Task;

class Demo extends Task
{
    protected function handle()
    {
        echo 'task handle: '.time().', unique id: '.$this->getUniqueId().PHP_EOL;
        // sleep(1);
        return 'result ok';
    }
}
