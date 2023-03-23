<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2023-03-23 11:18:53 +0800
 */

namespace Teddy\Lock;

class Key
{
    protected string $key;

    protected string $name;

    protected string $token = '';

    public function __construct(string $key)
    {
        $this->key  = $key;
        $this->name = 'teddyLock:'.$key;
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUniqueToken(): string
    {
        if (!$this->token) {
            $this->token = time().uniqid();
        }

        return $this->token;
    }
}
