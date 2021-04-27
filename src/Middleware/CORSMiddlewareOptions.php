<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-04-27 11:24:24 +0800
 */

namespace Teddy\Middleware;

use Illuminate\Support\Arr;
use Teddy\Abstracts\AbstractOptions;

class CORSMiddlewareOptions extends AbstractOptions
{
    protected function initialize(): void
    {
        $this->data = [
            'conditions' => [
                'path'   => null,
                'ignore' => null,
            ],
            'intercept'       => true,
            'origin'          => '*',
            'methods'         => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
            'withCredentials' => false,
            'headers'         => [
                'Accept',
                'Accept-Language',
                'User-Agent',
                'X-Requested-With',
                'If-Modified-Since',
                'Cache-Control',
                'Content-Type',
                'Range',
                'Authorization',
            ],
        ];

        $this->keys = array_keys($this->data);
    }

    protected function setMethods($methods): void
    {
        $methods = Arr::wrap($methods);
        if (isset($methods['set'])) {
            $this->data['methods'] = Arr::wrap($methods['set']);
        } else {
            $this->data['methods'] = array_merge($this->data['methods'], $methods);
        }
    }

    protected function setHeaders($headers): void
    {
        $headers = Arr::wrap($headers);
        if (isset($headers['set'])) {
            $this->data['headers'] = Arr::wrap($headers['set']);
        } else {
            $this->data['headers'] = array_merge($this->data['headers'], $headers);
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
