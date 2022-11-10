<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 17:27:57 +0800
 */

namespace Teddy\Interfaces;

interface WebsocketHandlerInterface
{
    public function onConnect(WebsocketConnectionInterface $connection);

    public function onClose(WebsocketConnectionInterface $connection);

    public function onMessage(WebsocketConnectionInterface $connection, $data);
}
