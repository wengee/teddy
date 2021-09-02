<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-08-30 09:45:41 +0800
 */

namespace Teddy\Auth;

use Illuminate\Support\Str;
use Teddy\Exception;

class Manager
{
    public const CACHE_KEY = 'auth:token:';

    protected $secret;

    public function __construct()
    {
        $this->secret = config('auth.secret', '** It is the default auth secret. **');
    }

    public function create(array $data, int $expiresIn = 0): string
    {
        $token    = $this->generateToken();
        $cacheKey = self::CACHE_KEY.$token;
        if ($expiresIn > 0) {
            $ret = app('redis')->set($cacheKey, $data, $expiresIn);
        } else {
            $ret = app('redis')->set($cacheKey, $data);
        }

        if (!$ret) {
            throw new Exception('Can not save the auth data.');
        }

        return $token;
    }

    public function refresh(string $token, int $expiresIn = 0): ?string
    {
        if (!$this->checkToken($token, true)) {
            return null;
        }

        $data = $this->fetch($token);
        if (!$data) {
            return null;
        }

        return $this->create($data, $expiresIn);
    }

    public function fetch(string $token): ?array
    {
        if (!$this->checkToken($token, true)) {
            return null;
        }

        $cacheKey = self::CACHE_KEY.$token;
        $data     = app('redis')->get($cacheKey);

        return ($data && is_array($data)) ? $data : null;
    }

    public function clear(string $token): void
    {
        if ($this->checkToken($token, true)) {
            app('redis')->del(self::CACHE_KEY.$token);
        }
    }

    public function ttl(string $token, int $expiresIn = 0): void
    {
        if ($this->checkToken($token, true) && $expiresIn > 0) {
            app('redis')->expire(self::CACHE_KEY.$token, $expiresIn);
        }
    }

    public function reset(string $token, array $data, int $expiresIn = 0): void
    {
        $this->checkToken($token);

        $cacheKey = self::CACHE_KEY.$token;
        if ($expiresIn > 0) {
            $ret = app('redis')->set($cacheKey, $data, $expiresIn);
        } else {
            $ret = app('redis')->set($cacheKey, $data);
        }

        if (!$ret) {
            throw new Exception('Can not save the auth data.');
        }
    }

    public function update(string $token, $key, $value)
    {
        $this->checkToken($token);

        $cacheKey = self::CACHE_KEY.$token;
        $data     = app('redis')->get($cacheKey);
        if (false === $data || !is_array($data)) {
            throw new Exception('Can not found the auth data.');
        }

        $data[$key] = $value;

        return app('redis')->set($cacheKey, $data);
    }

    public function remove(string $token, $key): void
    {
        $this->checkToken($token);

        $cacheKey = self::CACHE_KEY.$token;
        $data     = app('redis')->get($cacheKey);
        if (false === $data || !is_array($data)) {
            throw new Exception('Can not found the auth data.');
        }

        unset($data[$key]);
        app('redis')->set($cacheKey, $data);
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
        return md5($timestamp.$this->secret.$nonceStr);
    }
}
