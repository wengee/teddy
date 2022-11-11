<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-10 17:27:57 +0800
 */

namespace Teddy\Interfaces;

interface WebsocketConnectionInterface
{
    public function getId(): int;

    public function getRemoteIp(): string;

    public function getRemotePort(): int;

    public function send($data, bool $raw = false);

    public function close();
}
