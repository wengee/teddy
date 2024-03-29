<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-22 15:48:45 +0800
 */

namespace Teddy\Auth;

use RuntimeException;
use Teddy\Auth\Adapaters\JwtAdapater;
use Teddy\Auth\Adapaters\RedisAdapater;
use Teddy\Interfaces\AuthAdapaterInterface;
use Teddy\Interfaces\ContainerAwareInterface;
use Teddy\Traits\ContainerAwareTrait;

class AuthManager implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected AuthAdapaterInterface $adapater;

    public function __construct()
    {
        $options = (array) config('auth', []);

        $this->adapater = $this->createAdapater($options['adapater'] ?? 'redis', $options);
    }

    public function login(array $data, int $expiresIn = 3600): string
    {
        return $this->adapater->encode($data, $expiresIn);
    }

    public function logout(string $token): void
    {
        $this->adapater->block($token);
    }

    public function load(string $token): ?array
    {
        return $this->adapater->decode($token);
    }

    public function refresh(string $token, int $expiresIn = 3600, int $cachedTTL = 0): string
    {
        $data = $this->adapater->decode($token);
        $this->adapater->block($token, $cachedTTL);

        return $this->adapater->encode($data, $expiresIn);
    }

    protected function createAdapater(string $adapater, array $options): AuthAdapaterInterface
    {
        if ('redis' === $adapater) {
            $adapater = RedisAdapater::class;
        } elseif ('jwt' === $adapater) {
            $adapater = JwtAdapater::class;
        }

        if (!is_subclass_of($adapater, AuthAdapaterInterface::class)) {
            throw new RuntimeException('The auth adapater ['.$adapater.'] is invalid.');
        }

        return make($adapater, [$options]);
    }
}
