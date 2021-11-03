<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-11-03 11:42:05 +0800
 */

namespace Teddy\Abstracts;

use Teddy\Interfaces\ConfigTagInterface;

abstract class AbstractConfigTag implements ConfigTagInterface
{
    protected $originValue;

    protected $parsed = false;

    protected $parsedValue;

    public function __construct($value)
    {
        $this->originValue = $value;
    }

    public function getValue()
    {
        if (!$this->parsed) {
            $this->parsedValue = $this->parseValue($this->originValue);
            $this->parsed      = true;
        }

        return $this->parsedValue;
    }

    abstract protected function parseValue($value);
}
