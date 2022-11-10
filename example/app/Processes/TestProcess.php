<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 16:55:09 +0800
 */

namespace App\Processes;

use Teddy\Abstracts\AbstractProcess;

class TestProcess extends AbstractProcess
{
    protected $count = 0;

    public function handle(): void
    {
        log_message(null, 'INFO', 'test process start');
    }

    public function onReload(): void
    {
    }
}
