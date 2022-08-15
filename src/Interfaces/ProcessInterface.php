<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-15 15:40:36 +0800
 */

namespace Teddy\Interfaces;

use Swoole\Process;
use Workerman\Worker;

interface ProcessInterface
{
    public function getName(): string;

    public function getListen(): string;

    public function getContext(): array;

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
}
