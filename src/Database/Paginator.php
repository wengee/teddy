<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 11:02:47 +0800
 */

namespace Teddy\Database;

class Paginator
{
    public array $items = [];

    public int $totalItems;

    public int $totalPages;

    public int $pageSize;

    public int $first;

    public int $before;

    public int $current;

    public int $next;

    public int $last;

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
