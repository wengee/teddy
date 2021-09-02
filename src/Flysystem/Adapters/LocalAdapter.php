<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-31 17:33:03 +0800
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
