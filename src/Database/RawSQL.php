<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:13:31 +0800
 */

namespace Teddy\Database;

class RawSQL
{
    /** @var string */
    protected $sql = '';

    /** @var array */
    protected $data = [];

    public function __construct(?string $sql = null, ...$data)
    {
        if ($sql) {
            $this->setSQL($sql);
            $this->setData(...$data);
        }
    }

    public function setSQL(string $sql): void
    {
        $this->sql = $sql;
    }

    public function setData(...$data): void
    {
        $this->data = $data;
    }

    public function toSql(array &$map = [], ?QueryBuilder $query = null): string
    {
        if (empty($this->sql)) {
            return '';
        }

        $sql = preg_replace_callback('#\[([\\w\\.]+)\]#', function (array $m) use ($query) {
            $column = $m[1];
            if ($query) {
                $column = $query->toDbColumn($column);
            }

            return $column;
        }, $this->sql);

        if (!empty($this->data)) {
            $map = array_merge($map, $this->data);
        }

        return $sql;
    }
}
