<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 17:55:10 +0800
 */

namespace App;

use Teddy\Interfaces\WebsocketConnectionInterface;
use Teddy\Interfaces\WebsocketHandlerInterface;

class WebsocketHandler implements WebsocketHandlerInterface
{
    public function onConnect(WebsocketConnectionInterface $connection): void
    {
        $connection->send('onConnect');
    }

    public function onMessage(WebsocketConnectionInterface $connection, $data): void
    {
        if ('exit' === $data) {
            $connection->close();
        } else {
            $connection->send('Receive: '.$data);
        }
    }

    public function onClose(WebsocketConnectionInterface $connection): void
    {
        $connection->send('onClose');
    }
}
