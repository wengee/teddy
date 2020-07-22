<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2020-07-22 20:11:45 +0800
 */

namespace Teddy\Auth;

use Illuminate\Support\Str;
use Teddy\Exception;

class Manager
{
    public const CACHE_KEY = 'auth:token:';

    public function create(array $data, int $expiresIn = 0): string
    {
        $token = $this->generateToken();
        $cacheKey = self::CACHE_KEY . $token;
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
        $data = $this->fetch($token);
        if (!$data) {
            return null;
        }

        return $this->create($data, $expiresIn);
    }

    public function fetch(string $token): ?array
    {
        $cacheKey = self::CACHE_KEY . $token;
        $data = app('redis')->get($cacheKey);
        return ($data && is_array($data)) ? $data : null;
    }

    public function clear(string $token): void
    {
        app('redis')->del(self::CACHE_KEY . $token);
    }

    public function ttl(string $token, int $expiresIn = 0): void
    {
        if ($expiresIn > 0) {
            app('redis')->expire(self::CACHE_KEY . $token, $expiresIn);
        }
    }

    public function reset(string $token, array $data, int $expiresIn = 0): void
    {
        $cacheKey = self::CACHE_KEY . $token;
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
        $cacheKey = self::CACHE_KEY . $token;
        $data = app('redis')->get($cacheKey);
        if ($data === false || !is_array($data)) {
            throw new Exception('Can not found the auth data.');
        }

        $data[$key] = $value;
        return app('redis')->set($cacheKey, $data);
    }

    public function remove(string $token, $key): void
    {
        $cacheKey = self::CACHE_KEY . $token;
        $data = app('redis')->get($cacheKey);
        if ($data === false || !is_array($data)) {
            throw new Exception('Can not found the auth data.');
        }

        unset($data[$key]);
        app('redis')->set($cacheKey, $data);
    }

    protected function generateToken(): string
    {
        return intval(microtime(true) * 1000) . '.' . md5(uniqid() . Str::random(8));
    }
}
