<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-08-15 10:31:42 +0800
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
        return ($this->ssl ? 'https://' : 'http://') . ($this->cdnDomain ?: ($this->bucket . '.' . $this->endPoint)) . '/' . ltrim($path, '/');
    }
}
