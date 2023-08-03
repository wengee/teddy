<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-08-03 17:39:14 +0800
 */

namespace Teddy\Database;

class Paginator
{
    public array $items = [];

    public int $totalItems = 0;

    public int $totalPages = 0;

    public int $pageSize = 0;

    public int $first = 0;

    public int $before = 0;

    public int $current = 0;

    public int $next = 0;

    public int $last = 0;

    public function __construct($items, int $total, int $pageSize, int $currentPage = 0)
    {
        $totalPages  = (int) max(1, ceil($total / $pageSize));
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
