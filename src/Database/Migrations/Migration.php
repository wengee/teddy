<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-06-12 11:13:53 +0800
 */

namespace Teddy\Database\Migrations;

abstract class Migration
{
    protected $batch = null;

    protected $version = null;

    public function getBatch(): ?int
    {
        return $this->batch;
    }

    public function getVersion(): string
    {
        return $this->version ?: '1.0.0';
    }
}
