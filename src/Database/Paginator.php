<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Database;

class Paginator
{
    public $items = [];

    public $totalItems;

    public $totalPages;

    public $pageSize;

    public $first;

    public $before;

    public $current;

    public $next;

    public $last;

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
