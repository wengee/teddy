<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy;

class Base64
{
    public function encode(string $data, bool $urlSafe = false): string
    {
        $ret = base64_encode($data);
        if ($urlSafe) {
            $ret = rtrim(strtr($ret, '+/', '-_'), '=');
        }

        return $ret;
    }

    public function encodeUrl(string $data): string
    {
        return $this->encode($data, true);
    }

    public function decode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public function serialize($data, bool $urlSafe = false): string
    {
        return $this->encode(serialize($data), $urlSafe);
    }

    public function serializeUrl($data): string
    {
        return $this->serialize($data, true);
    }

    public function unserialize(string $data)
    {
        return unserialize($this->decode($data));
    }
}
