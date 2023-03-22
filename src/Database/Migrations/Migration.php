<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 16:55:36 +0800
 */

namespace Teddy\Database\Migrations;

abstract class Migration
{
    protected int $batch = 0;

    protected string $version = '';

    public function getBatch(): int
    {
        return $this->batch;
    }

    public function getVersion(): string
    {
        return $this->version ?: '1.0.0';
    }
}
