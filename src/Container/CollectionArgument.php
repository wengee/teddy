<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 15:44:10 +0800
 */

namespace Teddy\Container;

use Teddy\Interfaces\CollectionArgumentInterface;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Traits\ContainerAwareTrait;
use Teddy\Traits\ResolveArgumentsTrait;

class CollectionArgument implements CollectionArgumentInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;
    use ResolveArgumentsTrait;

    protected array $value = [];

    protected bool $resolved = false;

    protected array $resolvedValue = [];

    public function __construct(array $value = [])
    {
        $this->value = $value;
    }

    public function getValue(): array
    {
        if (!$this->resolved) {
            $this->resolvedValue = $this->resolveArguments($this->value);
            $this->resolved      = true;
        }

        return $this->resolvedValue;
    }
}
