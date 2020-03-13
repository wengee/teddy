<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-03-13 17:48:16 +0800
 */

namespace Teddy\Database\Migrations;

abstract class Migration
{
    protected $batch = null;

    public function getBatch(): ?int
    {
        return $this->batch;
    }
}
