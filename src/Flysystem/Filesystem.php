<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-31 18:01:03 +0800
 */

namespace Teddy\Flysystem;

use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\PathNormalizer;
use RuntimeException;

class Filesystem extends LeagueFilesystem
{
    /** @var FilesystemAdapter */
    protected $myAdapter;

    public function __construct(
        FilesystemAdapter $adapter,
        array $config = [],
        PathNormalizer $pathNormalizer = null
    ) {
        $this->myAdapter = $adapter;
        parent::__construct($adapter, $config, $pathNormalizer);
    }

    public function url($path)
    {
        if (method_exists($this->myAdapter, 'getUrl')) {
            return $this->myAdapter->getUrl($path);
        }

        throw new RuntimeException('This driver does not support retrieving URLs.');
    }
}
