<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-05-06 14:18:52 +0800
 */

namespace Teddy\Facades;

/**
 * @method static \Teddy\Filter add(string $name, callable|object $handler)
 * @method static mixed sanitize(mixed $value, array|string $filters, bool $noRecursive = true)
 * @method static array getFilters()
 */
class Filter extends Facade
{
    public static function getFacadeAccessor(): string
    {
        return 'filter';
    }
}
