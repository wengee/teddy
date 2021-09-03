<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Container;

use InvalidArgumentException;
use Teddy\Interfaces\LiteralArgumentInterface;

class LiteralArgument implements LiteralArgumentInterface
{
    public const TYPE_ARRAY    = 'array';
    public const TYPE_BOOL     = 'boolean';
    public const TYPE_BOOLEAN  = 'boolean';
    public const TYPE_CALLABLE = 'callable';
    public const TYPE_DOUBLE   = 'double';
    public const TYPE_FLOAT    = 'double';
    public const TYPE_INT      = 'integer';
    public const TYPE_INTEGER  = 'integer';
    public const TYPE_OBJECT   = 'object';
    public const TYPE_STRING   = 'string';

    protected $value;

    public function __construct($value, string $type = null)
    {
        if (
            null === $type
            || (self::TYPE_CALLABLE === $type && is_callable($value))
            || (self::TYPE_OBJECT === $type && is_object($value))
            || gettype($value) === $type
        ) {
            $this->value = $value;
        } else {
            throw new InvalidArgumentException('Incorrect type for value.');
        }
    }

    public function getValue()
    {
        return $this->value;
    }
}
