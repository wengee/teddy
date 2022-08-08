<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:18:07 +0800
 */

namespace Teddy\Database;

use Illuminate\Support\Traits\Macroable;

abstract class Grammar
{
    use Macroable;

    /**
     * The grammar table prefix.
     *
     * @var string
     */
    protected $tablePrefix = '';

    /**
     * Wrap an array of values.
     */
    public function wrapArray(array $values): array
    {
        return array_map([$this, 'wrap'], $values);
    }

    public function wrapTable($table): string
    {
        return $this->wrap($this->tablePrefix.$table);
    }

    public function wrap($value): string
    {
        if (false !== stripos($value, ' as ')) {
            $segments = preg_split('/\s+as\s+/i', $value);

            return $this->wrapValue($segments[0]).' AS '.$this->wrapValue($segments[1]);
        }

        return $this->wrapValue($value);
    }

    public function columnize(array $columns): string
    {
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    public function parameterize(array $values): string
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    public function parameter($value): string
    {
        return '?';
    }

    public function quoteString($value): string
    {
        if (is_array($value)) {
            return implode(', ', array_map([$this, __FUNCTION__], $value));
        }

        return "'{$value}'";
    }

    public function getTablePrefix(): ?string
    {
        return $this->tablePrefix;
    }

    public function setTablePrefix(string $prefix)
    {
        $this->tablePrefix = $prefix;

        return $this;
    }

    protected function wrapValue(string $value): string
    {
        if ('*' !== $value) {
            return '"'.str_replace('.', '"."', $value).'"';
        }

        return '"'.$value.'"';
    }
}
