<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-07-26 11:09:53 +0800
 */

namespace Teddy\Flysystem\Adapters;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Teddy\Interfaces\TeddyFilesystemAdapter;

class LocalAdapter extends LocalFilesystemAdapter implements TeddyFilesystemAdapter
{
    protected string $urlPrefix = '';

    public function __construct(array $config)
    {
        $this->urlPrefix = rtrim($config['url'] ?? '', '/');
        $location        = $config['location'] ?? ($config['root'] ?? '');
        parent::__construct($location);
    }

    public function getUrl(string $path): string
    {
        return $this->urlPrefix.'/'.ltrim($path, '/');
    }
}
