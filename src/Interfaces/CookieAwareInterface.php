<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface CookieAwareInterface
{
    public function setCookie(string $name, ?string $value = null, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = true): ResponseInterface;

    public function getCookies(): array;
}
