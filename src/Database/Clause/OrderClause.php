<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-14 15:31:19 +0800
 */

namespace Teddy\Database\Clause;

class OrderClause extends ClauseContainer
{
    public function orderBy(string $column, string $direction = 'ASC')
    {
        $column = $this->query->toDbColumn($column);
        $this->container[] = $column . ' ' . strtoupper($direction);
    }

    public function order(string $column, string $direction = 'ASC')
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
