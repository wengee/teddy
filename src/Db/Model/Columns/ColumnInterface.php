<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-01 18:01:01 +0800
 */
namespace Teddy\Db\Model\Columns;

interface ColumnInterface
{
    public function getName(): string;

    public function getField(): ?string;

    public function isPrimaryKey(): bool;

    public function isAutoIncrement(): bool;

    public function dbValue($value);

    public function value($value);

    public function defaultValue();
}
