<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-15 17:12:43 +0800
 */

namespace Teddy\Database\Migrations;

abstract class Migration
{
    /**
     * @var int
     */
    protected $batch = 0;

    /**
     * @var string
     */
    protected $version = '';

    public function getBatch(): int
    {
        return $this->batch;
    }

    public function getVersion(): string
    {
        return $this->version ?: '1.0.0';
    }
}
