<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-12-17 18:32:05 +0800
 */

namespace Teddy\Flysystem;

use Overtrue\Flysystem\Cos\CosAdapter as OvertrueCosAdapter;

class CosAdapter extends OvertrueCosAdapter
{
    public function getTemporaryUrl($path, $expiration, array $options = [])
    {
        $options = array_merge($options, ['Scheme' => $this->config['scheme'] ?? 'http']);

        $expiration = date('c', !\is_numeric($expiration) ? \strtotime($expiration) : \intval($expiration));

        $objectUrl = $this->getClient()->getObjectUrl(
            $this->getBucket(),
            $path,
            $expiration,
            $options
        );

        if ($this->config['cdn'] && $this->config['read_from_cdn']) {
            $url = parse_url($objectUrl);
            return \sprintf('%s%s?%s', \rtrim($this->config['cdn'], '/'), urldecode($url['path']), $url['query']);
        }

        return $objectUrl;
    }
}
