<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-06 14:26:26 +0800
 */

namespace Teddy\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface AuthHandlerInterface
{
    public function handle(ServerRequestInterface $request, array $payload): ServerRequestInterface;
}
