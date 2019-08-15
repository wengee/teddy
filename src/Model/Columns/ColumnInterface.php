<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Model\Columns;

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
