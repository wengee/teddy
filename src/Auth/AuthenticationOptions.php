<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 11:23:49 +0800
 */

namespace Teddy\Auth;

use Illuminate\Support\Arr;
use Teddy\Abstracts\AbstractOptions;

class AuthenticationOptions extends AbstractOptions
{
    protected function initialize(): void
    {
        $this->data = [
            'conditions' => [
                'path'   => null,
                'ignore' => null,
            ],
            'header'    => 'Authorization',
            'regexp'    => '/^Bearer\\s+(.*)$/i',
            'cookie'    => 'token',
            'param'     => 'token',
            'attribute' => 'user',
            'callback'  => null,
        ];

        $this->keys = array_keys($this->data);
    }

    protected function setCallback($callback): void
    {
        if (\is_callable($callback)) {
            $this->data['callback'] = $callback;
        }
    }

    protected function setPath($path): void
    {
        $this->data['conditions']['path'] = Arr::wrap($path);
    }

    protected function setIgnore($ignore): void
    {
        $this->data['conditions']['ignore'] = Arr::wrap($ignore);
    }
}
