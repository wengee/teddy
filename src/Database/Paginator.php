<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:16:47 +0800
 */

namespace Teddy\Database;

class Paginator
{
    /** @var array */
    public $items = [];

    /** @var int */
    public $totalItems = 0;

    /** @var int */
    public $totalPages = 0;

    /** @var int */
    public $pageSize = 0;

    /** @var int */
    public $first = 0;

    /** @var int */
    public $before = 0;

    /** @var int */
    public $current = 0;

    /** @var int */
    public $next = 0;

    /** @var int */
    public $last = 0;

    public function __construct($items, int $total, int $pageSize, int $currentPage = 0)
    {
        $totalPages  = max(1, ceil($total / $pageSize));
        $currentPage = max(1, $currentPage);

        $this->items      = $items;
        $this->totalItems = $total;
        $this->totalPages = $totalPages;
        $this->pageSize   = $pageSize;

        $this->first   = 1;
        $this->before  = max(1, $currentPage - 1);
        $this->current = $currentPage;
        $this->next    = min($currentPage + 1, $totalPages);
        $this->last    = $totalPages;
    }
}
