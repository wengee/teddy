<?php declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-11-06 14:38:51 +0800
 */

namespace Teddy\Lock;

class Key
{
    protected $key;

    protected $token;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function __toString(): string
    {
        return 'teddyLock:' . $this->key;
    }

    public function getUniqueToken(): string
    {
        if (!$this->token) {
            $this->token = time() . uniqid();
        }

        return $this->token;
    }
}
