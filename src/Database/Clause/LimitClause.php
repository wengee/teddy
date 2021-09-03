<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Database\Clause;

class LimitClause extends ClauseContainer
{
    private $limit;

    private $offset = 0;

    public function limit(int $number, int $offset = 0): void
    {
        if ($offset >= 0) {
            $this->offset = intval($offset);
        }

        $this->limit = intval($number);
    }

    public function offset(int $number = 0): void
    {
        if ($number >= 0) {
            $this->offset = intval($number);
        }
    }

    public function toSql(&$map = []): string
    {
        if (null === $this->limit && 0 === $this->offset) {
            return '';
        }

        $ret = '';
        if (null !== $this->limit) {
            $ret .= ' LIMIT '.$this->limit;
        }

        if ($this->offset > 0) {
            $ret .= ' OFFSET '.$this->offset;
        }

        return $ret;
    }
}
