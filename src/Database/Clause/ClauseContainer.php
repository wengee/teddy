<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-07 12:45:27 +0800
 */

namespace Teddy\Database\Clause;

use Teddy\Database\QueryBuilder;

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
