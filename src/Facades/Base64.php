<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-12 16:39:20 +0800
 */

namespace Teddy\Facades;

use Teddy\Base64 as TeddyBase64;

/**
 * @method static string encode(string $data, bool $urlSafe = false)
 * @method static string encodeUrl(string $data)
 * @method static string decode(string $data)
 * @method static string serialize(mixed $data, bool $urlSafe = false)
 * @method static string serializeUrl($data)
 * @method static mixed unserialize(string $data)
 */
class Base64 extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TeddyBase64::class;
    }
}
