<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-12-16 17:24:11 +0800
 */

namespace Teddy\Interfaces;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter as LeagueFilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToWriteFile;

interface FilesystemAdapter extends LeagueFilesystemAdapter
{
    /**
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function append(string $path, string $contents, Config $config): void;

    /**
     * @param resource $contents
     *
     * @throws UnableToWriteFile
     * @throws FilesystemException
     */
    public function appendStream(string $path, $contents, Config $config): void;

    public function getUrl(string $path): string;
}
