<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2022-08-08 17:31:04 +0800
 */

namespace Teddy\Auth\Adapaters;

use Exception;
use Illuminate\Support\Str;
use Teddy\Config\Repository;
use Teddy\Interfaces\AuthAdapaterInterface;
use Teddy\Redis\Redis;

class RedisAdapater implements AuthAdapaterInterface
{
    /**
     * @var array
     */
    protected $options = [];

    public function __construct(array $options)
    {
        $this->options = (new Repository([
            'prefix'     => 'auth:token:',
            'secret'     => '** It is the default auth secret. **',
            'connection' => 'default',
        ]))->merge($options)->toArray();
    }

    public function encode(array $data, int $expiresIn = 0): string
    {
        $token    = $this->generateToken();
        $cacheKey = $this->options['prefix'].$token;
        if ($expiresIn > 0) {
            $ret = $this->redis()->set($cacheKey, $data, $expiresIn);
        } else {
            $ret = $this->redis()->set($cacheKey, $data);
        }

        if (!$ret) {
            throw new Exception('Can not encode the auth data.');
        }

        return $token;
    }

    public function decode(string $token): ?array
    {
        if (!$this->checkToken($token, true)) {
            return null;
        }

        $cacheKey = $this->options['prefix'].$token;
        $data     = $this->redis()->get($cacheKey);

        return ($data && is_array($data)) ? $data : null;
    }

    public function block(string $token): void
    {
        if (!$this->checkToken($token, true)) {
            return;
        }

        $cacheKey = $this->options['prefix'].$token;
        $this->redis()->del($cacheKey);
    }

    protected function redis(): Redis
    {
        return app('redis')->connection($this->options['connection']);
    }

    protected function generateToken(): string
    {
        $timestamp = intval(microtime(true) * 1000);
        $nonceStr  = strtolower(uniqid().Str::random(8));

        return $timestamp.'.'.$nonceStr.'.'.$this->makeSignature($timestamp, $nonceStr);
    }

    protected function checkToken(string $token, bool $silent = false): bool
    {
        $ret = false;
        $arr = explode('.', $token);
        if (!$arr || 3 !== count($arr)) {
            goto RESULT;
        }

        $signature = $this->makeSignature((int) $arr[0], $arr[1]);
        $ret       = $signature === $arr[2];

        RESULT:
        if ($silent) {
            return $ret;
        }
        if (!$ret) {
            throw new Exception('Token is invalid.');
        }

        return true;
    }

    protected function makeSignature(int $timestamp, string $nonceStr): string
    {
        return md5($timestamp.$this->options['secret'].$nonceStr);
    }
}
