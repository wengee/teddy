<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 10:45:20 +0800
 */

namespace Teddy\Config;

use Illuminate\Support\Arr;
use Nette\Schema\Processor;
use Nette\Schema\Schema;
use Teddy\Interfaces\ArrayableInterface;

class Repository implements ArrayableInterface
{
    public const DATA_PROTECTED = 1;
    public const DATA_AS_LIST   = 2;
    public const DATA_AS_RAW    = 4;

    protected array $items = [];

    /** @var mixed */
    protected $value;

    protected int $flags = 0;

    protected ?Schema $schema;

    public function __construct($items = [], int $flags = 0, ?Schema $schema = null)
    {
        if (!is_array($items)) {
            $flags = $flags | self::DATA_AS_RAW;
        }

        if (($flags & self::DATA_AS_RAW) === self::DATA_AS_RAW) {
            $this->value = $items;
        } else {
            $this->items = $items;
        }

        $this->flags  = $flags;
        $this->schema = $schema;
    }

    public function setFlags(int $flags): self
    {
        $this->flags = $flags;

        return $this;
    }

    public function getFlags(): int
    {
        return $this->flags;
    }

    public function addFlags(int $flags): self
    {
        $this->flags = $this->flags | $flags;

        return $this;
    }

    public function setSchema(Schema $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    public function hasFlag(int $flag): bool
    {
        return ($this->flags & $flag) === $flag;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function merge($items, bool $custom = false): self
    {
        if ($custom && $this->hasFlag(self::DATA_PROTECTED)) {
            return $this;
        }

        if ($this->hasFlag(self::DATA_AS_RAW)) {
            $this->value = ($items instanceof Repository) ? $items->parse() : $items;

            return $this;
        }

        if ($items instanceof Repository) {
            $this->addFlags($items->getFlags());

            $items = $items->all();
        }

        if (!is_array($items)) {
            return $this;
        }

        if ($this->hasFlag(self::DATA_AS_LIST)) {
            if (isset($items['$set'])) {
                $this->items = Arr::wrap($items['$set']);
            } else {
                $value       = isset($items['$merge']) ? Arr::wrap($items['$merge']) : $items;
                $this->items = array_values(array_merge($this->items, $value));
            }
        } else {
            foreach ($items as $key => $value) {
                if (!is_string($key)) {
                    continue;
                }

                if (isset($this->items[$key]) && ($this->items[$key] instanceof Repository)) {
                    $this->items[$key]->merge($value, $custom);
                } else {
                    $this->items[$key] = $value;
                }
            }
        }

        if ($this->schema) {
            (new Processor())->process($this->schema, $this->parse());
        }

        return $this;
    }

    public function parse()
    {
        if ($this->hasFlag(self::DATA_AS_RAW)) {
            return $this->value;
        }

        $arr = [];
        foreach ($this->items as $key => $value) {
            if ($value instanceof Repository) {
                $arr[$key] = $value->parse();
            } else {
                $arr[$key] = $value;
            }
        }

        return $this->hasFlag(self::DATA_AS_LIST) ? array_values($arr) : $arr;
    }

    public function toArray(): array
    {
        $arr = $this->parse();

        return is_array($arr) ? $arr : [];
    }
}
