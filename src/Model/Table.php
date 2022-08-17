<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-17 17:36:01 +0800
 */

namespace Teddy\Model;

use Attribute;
use Exception;
use Illuminate\Support\Str;

#[Attribute(Attribute::TARGET_CLASS)]
class Table
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var bool
     */
    protected $dynamic = false;

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

    public function getDynamic(): bool
    {
        return $this->dynamic;
    }

    public function getDynamicName(?string $suffix): string
    {
        if (!$this->dynamic) {
            return $this->getName();
        }

        if (!$suffix) {
            throw new Exception('Table suffix is required.');
        }

        return strtr($this->name, '{SUFFIX}', $suffix);
    }
}
