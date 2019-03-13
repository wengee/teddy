<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-01-07 16:18:57 +0800
 */
namespace SlimExtra\Db;

class RawSQL
{
    protected $sql;

    protected $data = [];

    public function __construct(?string $sql = null, ...$data)
    {
        if ($sql) {
            $this->setSQL($sql);
            $this->setData(...$data);
        }
    }

    public function setSQL(string $sql)
    {
        $this->sql = $sql;
    }

    public function setData(...$data)
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
