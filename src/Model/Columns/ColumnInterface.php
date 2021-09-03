<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Model\Columns;

interface ColumnInterface
{
    public function getName(): string;

    public function getField(): ?string;

    public function isPrimaryKey(): bool;

    public function isAutoIncrement(): bool;

    public function convertToDbValue($value);

    public function convertToPhpValue($value);

    public function defaultValue();
}
