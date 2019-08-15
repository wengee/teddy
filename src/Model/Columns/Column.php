<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
 */

namespace Teddy\Model\Columns;

use Illuminate\Support\Str;

abstract class Column implements ColumnInterface
{
    /** @Required */
    protected $name;

    protected $field;

    protected $primaryKey = false;

    protected $autoIncrement = false;

    protected $default = null;

    public function __construct(array $values)
    {
        foreach ($values as $key => $value) {
            if ($key === 'value') {
                $this->name = $value;
                continue;
            }

            $method = 'set' . Str::studly($key);
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            } elseif (property_exists($this, $key)) {
                $this->{$key} = $value;
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
        return $this->default;
    }

    abstract public function dbValue($value);

    abstract public function value($value);
}
