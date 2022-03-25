<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-03-14 16:18:15 +0800
 */

namespace Teddy\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface CookieAwareInterface
{
    public function setCookie(string $name, ?string $value = null, int $maxAge = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true, bool $sameSite = false): ResponseInterface;

    public function getCookies(): array;
}
