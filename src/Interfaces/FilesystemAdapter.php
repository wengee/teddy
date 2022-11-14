<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-11-14 20:42:11 +0800
 */

namespace Teddy\Interfaces;

use League\Flysystem\FilesystemAdapter as LeagueFilesystemAdapter;

interface FilesystemAdapter extends LeagueFilesystemAdapter
{
    public function getUrl(string $path): string;
}
