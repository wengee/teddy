<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:53 +0800
 */

namespace App\Listeners;

use League\Event\AbstractListener;
use League\Event\EventInterface;

class OnWorkerStartListener extends AbstractListener
{
    public function handle(EventInterface $event): void
    {
        echo $event->getName() . PHP_EOL;
    }
}
