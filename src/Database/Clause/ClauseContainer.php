<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:56:16 +0800
 */

namespace Teddy\Database\Clause;

use Teddy\Database\QueryBuilder;

abstract class ClauseContainer
{
    protected QueryBuilder $query;

    protected array $container = [];

    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    abstract public function toSql(&$map = []): string;
}
