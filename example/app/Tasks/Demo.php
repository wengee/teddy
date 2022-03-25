<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-20 17:27:14 +0800
 */

namespace App\Tasks;

use Teddy\Task;

class Demo extends Task
{
    protected $exclusive = false;

    protected function handle()
    {
        echo 'task ('.$this->id.') handle: '.time().PHP_EOL;
        // sleep(1);
        return 'result ok';
    }
}
