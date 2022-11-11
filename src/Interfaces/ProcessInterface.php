<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 16:20:06 +0800
 */

namespace Teddy\Interfaces;

use Swoole\Process;
use Workerman\Worker;

interface ProcessInterface
{
    public function getName(): string;

    public function getCount(): int;

    public function getOptions(): array;

    public function getOption(string $name, $default = null);

    /**
     * @param Process|Worker $worker
     */
    public function setWorker($worker);

    /**
     * @return Process|Worker
     */
    public function getWorker();

    public function handle();

    public function onReload();
}
