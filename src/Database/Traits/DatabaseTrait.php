<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-19 11:26:13 +0800
 */

namespace Teddy\Database\Traits;

use Teddy\Database\QueryBuilder;
use Teddy\Database\RawSQL;

trait DatabaseTrait
{
    public function table(string $model): QueryBuilder
    {
        return new QueryBuilder($this, $model);
    }

    public function raw(string $sql): RawSQL
    {
        return new RawSQL($sql);
    }
}
