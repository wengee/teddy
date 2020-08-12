<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-12 16:38:55 +0800
 */

namespace Teddy\Facades;

use Teddy\NumberString as TeddyNumberString;

/**
 * @method static string encode(int $num, string|int|null $base = null)
 * @method static int decode(string $num, string|int|null $base = null)
 */
class NumberString extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TeddyNumberString::class;
    }
}
