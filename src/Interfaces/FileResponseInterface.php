<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-26 15:24:09 +0800
 */

namespace Teddy\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface FileResponseInterface
{
    public function withSendFile(string $file): ResponseInterface;

    public function getSendFile(): string;
}
