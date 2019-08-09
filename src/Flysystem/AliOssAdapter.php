<?php
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-09 17:43:48 +0800
 */

namespace Teddy\Flysystem;

use Jacobcyl\AliOSS\AliOssAdapter as JacobcylAliOssAdapter;
use League\Flysystem\FileNotFoundException;

class AliOssAdapter extends JacobcylAliOssAdapter
{
    public function getUrl($path)
    {
        if (!$this->has($path)) {
            throw new FileNotFoundException($path.' not found');
        }

        $path = $this->applyPathPrefix($path);
        return ($this->ssl ? 'https://' : 'http://') . ($this->isCname ? ($this->cdnDomain == '' ? $this->endPoint : $this->cdnDomain) : $this->bucket . '.' . $this->endPoint) . '/' . ltrim($path, '/');
    }
}
