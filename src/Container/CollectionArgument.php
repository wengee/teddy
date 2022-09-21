<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-09-21 15:54:40 +0800
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

    /**
     * @var array
     */
    protected $value = [];

    /**
     * @var bool
     */
    protected $resolved = false;

    /**
     * @var array
     */
    protected $resolvedValue = [];

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
