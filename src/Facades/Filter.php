<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-08-12 16:39:20 +0800
 */

namespace Teddy\Facades;

use Teddy\Filter as TeddyFilter;

/**
 * @method static \Teddy\Filter add(string $name, object|callable $handler)
 * @method static mixed sanitize(mixed $value, string|array $filters, bool $noRecursive = true)
 * @method static array getFilters()
 */
class Filter extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return TeddyFilter::class;
    }
}
