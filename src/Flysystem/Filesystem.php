<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-15 17:15:08 +0800
 */

namespace Teddy\Flysystem;

use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\PathNormalizer;
use RuntimeException;
use Teddy\Interfaces\TeddyFilesystemAdapter;

class Filesystem extends LeagueFilesystem
{
    /**
     * @var TeddyFilesystemAdapter
     */
    protected $myAdapter;

    public function __construct(
        TeddyFilesystemAdapter $adapter,
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
