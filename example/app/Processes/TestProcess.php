<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-17 20:26:09 +0800
 */

namespace App\Processes;

use Teddy\Abstracts\AbstractProcess;

class TestProcess extends AbstractProcess
{
    protected $name = 'test';

    protected $count = 1;

    public function handle(): void
    {
        $first = true;
        while (true) {
            if ($first) {
                $first = false;
                log_message('console', 'INFO', 'test process start');
            }

            sleep(10);
        }
    }

    public function onReload(): void
    {
    }
}
