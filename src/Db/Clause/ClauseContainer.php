<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-07 12:45:27 +0800
 */

namespace SlimExtra\Db\Clause;

use SlimExtra\Db\QueryBuilder;

abstract class ClauseContainer
{
    /**
     * @var QueryBuilder
     */
    protected $query;

    /**
     * @var array
     */
    protected $container = [];

    public function __construct(QueryBuilder $query)
    {
        $this->query = $query;
    }

    abstract public function toSql(&$map = []): string;
}
