<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Database\Clause;

class GroupClause extends ClauseContainer
{
    public function groupBy(string ...$columns): void
    {
        $columns         = $this->query->toDbColumn($columns);
        $this->container = array_merge($this->container, $columns);
    }

    public function group(string ...$columns): void
    {
        $this->groupBy(...$columns);
    }

    public function toSql(&$map = []): string
    {
        if (empty($this->container)) {
            return '';
        }

        return ' GROUP BY '.implode(' , ', $this->container);
    }
}
