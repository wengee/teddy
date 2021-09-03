<?php
declare(strict_types=1);
/**
 * This file is part of Teddy Framework.
 *
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2021-09-03 11:37:54 +0800
 */

namespace Teddy\Traits;

use Psr\Http\Message\ServerRequestInterface;

trait UriMatchAwareTrait
{
    protected function isUriMatch(ServerRequestInterface $request, array $options): bool
    {
        if (empty($options['path']) && empty($options['ignore'])) {
            return true;
        }

        $uri = '/'.$request->getUri()->getPath();
        $uri = preg_replace('#/+#', '/', $uri);

        if (!empty($options['ignore'])) {
            foreach ((array) $options['ignore'] as $ignore) {
                if ($this->isMatch($uri, $ignore)) {
                    return false;
                }
            }

            if (empty($options['path'])) {
                return true;
            }
        }

        if (!empty($options['path'])) {
            foreach ((array) $options['path'] as $path) {
                if ($this->isMatch($uri, $path)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function isMatch(string $uri, string $rule): bool
    {
        $type = null;
        $rule = rtrim($rule, '/');
        if (false !== strpos($rule, ':')) {
            [$type, $rule] = explode(':', $rule);
        }

        switch ($type) {
            case 'eq':
            case '=':
                $rule = "@^{$rule}(/?(\\?.*)?)?$@";

                break;

            case 're':
                break;

            default:
                $rule = "@^{$rule}(/.*)?$@";
        }

        return (bool) preg_match($rule, $uri);
    }
}
