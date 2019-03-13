<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-08 11:44:17 +0800
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
