<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Database\Clause;

class OrderClause extends ClauseContainer
{
    public function orderBy(string $column, string $direction = 'ASC'): void
    {
        $column = $this->query->toDbColumn($column);
        $this->container[] = $column . ' ' . strtoupper($direction);
    }

    public function order(string $column, string $direction = 'ASC'): void
    {
        $this->orderBy($column, $direction);
    }

    public function toSql(&$map = []): string
    {
        if (empty($this->container)) {
            return '';
        }

        return ' ORDER BY '.implode(' , ', $this->container);
    }
}
