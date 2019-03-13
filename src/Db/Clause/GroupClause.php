<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-07 14:33:05 +0800
 */

namespace SlimExtra\Db\Clause;

class GroupClause extends ClauseContainer
{
    public function groupBy(string ...$columns)
    {
        $columns = $this->query->toDbColumn($columns);
        $this->container = array_merge($this->container, $columns);
    }

    public function group(string ...$columns)
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
