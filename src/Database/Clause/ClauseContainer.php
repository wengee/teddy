<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:23:03 +0800
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
