<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:42:25 +0800
 */

namespace Teddy\Model;

use Exception;

class Manager
{
    /**
     * @var Meta[]
     */
    protected $metas = [];

    /**
     * @param Model|string $model
     */
    public function getMeta($model): Meta
    {
        if (!is_subclass_of($model, Model::class)) {
            throw new Exception('Invalid parameter.');
        }

        $className = is_string($model) ? $model : get_class($model);
        if (!isset($this->metas[$className])) {
            $this->metas[$className] = new Meta($className);
        }

        return $this->metas[$className];
    }
}
