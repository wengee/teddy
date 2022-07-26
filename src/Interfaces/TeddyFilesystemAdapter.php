<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 11:09:25 +0800
 */

namespace Teddy\Interfaces;

use League\Flysystem\FilesystemAdapter;

interface TeddyFilesystemAdapter extends FilesystemAdapter
{
    public function getUrl(string $path): string;
}
