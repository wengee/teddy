<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-14 16:03:21 +0800
 */
namespace App\Listeners;

use League\Event\AbstractListener;
use League\Event\EventInterface;

class ExampleListener extends AbstractListener
{
    public function handle(EventInterface $event)
    {
        echo $event->getName() . PHP_EOL;
    }
}