<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-12-16 14:30:35 +0800
 */

namespace Teddy\Flysystem;

use League\Flysystem\Filesystem as LeagueFilesystem;
use League\Flysystem\InvalidStreamProvided;
use League\Flysystem\PathNormalizer;
use RuntimeException;
use Teddy\Interfaces\FilesystemAdapter;

class Filesystem extends LeagueFilesystem
{
    /**
     * @var FilesystemAdapter
     */
    protected $myAdapter;

    public function __construct(
        FilesystemAdapter $adapter,
        array $config = [],
        PathNormalizer $pathNormalizer = null
    ) {
        $this->myAdapter = $adapter;
        parent::__construct($adapter, $config, $pathNormalizer);
    }

    public function append(string $location, string $contents, int $position, array $config = []): void
    {
        $this->myAdapter->append(
            $this->pathNormalizer->normalizePath($location),
            $contents,
            $position,
            $this->config->extend($config)
        );
    }

    public function appendStream(string $location, $contents, int $position, array $config = []): void
    {
        // @var resource $contents
        $this->assertIsResource($contents);
        $this->rewindStream($contents);
        $this->myAdapter->appendStream(
            $this->pathNormalizer->normalizePath($location),
            $contents,
            $position,
            $this->config->extend($config)
        );
    }

    public function url($path)
    {
        if (method_exists($this->myAdapter, 'getUrl')) {
            return $this->myAdapter->getUrl($path);
        }

        throw new RuntimeException('This driver does not support retrieving URLs.');
    }

    /**
     * @param mixed $contents
     */
    protected function assertIsResource($contents): void
    {
        if (false === is_resource($contents)) {
            throw new InvalidStreamProvided(
                'Invalid stream provided, expected stream resource, received '.gettype($contents)
            );
        }
        if ($type = 'stream' !== get_resource_type($contents)) {
            throw new InvalidStreamProvided(
                'Invalid stream provided, expected stream resource, received resource of type '.$type
            );
        }
    }

    /**
     * @param resource $resource
     */
    protected function rewindStream($resource): void
    {
        if (0 !== ftell($resource) && stream_get_meta_data($resource)['seekable']) {
            rewind($resource);
        }
    }
}
