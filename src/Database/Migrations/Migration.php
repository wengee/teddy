<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Database\Migrations;

abstract class Migration
{
    protected $batch;

    protected $version;

    public function getBatch(): ?int
    {
        return $this->batch;
    }

    public function getVersion(): string
    {
        return $this->version ?: '1.0.0';
    }
}
