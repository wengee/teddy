<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-02-08 09:47:41 +0800
 */

namespace Teddy\Model\Columns;

use Illuminate\Support\Str;

abstract class Column implements ColumnInterface
{
    protected $name;

    protected $field;

    protected $primaryKey = false;

    protected $autoIncrement = false;

    protected $default;

    public function __construct(...$values)
    {
        foreach ($values as $key => $value) {
            if (0 === $key) {
                $this->name = $value;

                continue;
            }

            if (is_string($key)) {
                $method = 'set'.Str::studly($key);
                if (method_exists($this, $method)) {
                    $this->{$method}($value);
                } elseif (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function isPrimaryKey(): bool
    {
        return (bool) $this->primaryKey;
    }

    public function isAutoIncrement(): bool
    {
        return (bool) $this->autoIncrement;
    }

    public function defaultValue()
    {
        return value($this->default);
    }

    abstract public function convertToDbValue($value);

    abstract public function convertToPhpValue($value);
}
