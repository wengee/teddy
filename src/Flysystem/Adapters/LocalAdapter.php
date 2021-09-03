<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Flysystem\Adapters;

use League\Flysystem\Local\LocalFilesystemAdapter;

class LocalAdapter extends LocalFilesystemAdapter
{
    protected $urlPrefix = '';

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
