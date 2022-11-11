<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-12 00:06:09 +0800
 */

namespace Teddy\Interfaces;

interface AuthAdapaterInterface
{
    public function __construct(array $options);

    public function encode(array $data, int $expiresIn = 0): string;

    public function decode(string $token): ?array;

    public function block(string $token, int $cachedTTL = 0);
}
