<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-04-09 14:39:16 +0800
 */
namespace Teddy\Traits;

use Psr\Http\Message\ServerRequestInterface;

trait HasUriMatch
{
    protected function isUriMatch(ServerRequestInterface $request, array $options)
    {
        if (empty($this->options['path']) && empty($this->options['ignore'])) {
            return true;
        }

        $uri = '/' . $request->getUri()->getPath();
        $uri = preg_replace('#/+#', '/', $uri);

        if (!empty($this->options['ignore'])) {
            foreach ((array) $this->options['ignore'] as $ignore) {
                if ($this->isMatch($uri, $ignore)) {
                    return false;
                }
            }

            if (empty($this->options['path'])) {
                return true;
            }
        }

        if (!empty($this->options['path'])) {
            foreach ((array) $this->options['path'] as $path) {
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
        if (strpos($rule, ':') !== false) {
            list($type, $rule) = explode(':', $rule);
        }

        switch ($type) {
            case 'eq':
            case '=':
                $rule = "@^{$rule}(/?\\?.*)?$@";
                break;

            case 're':
                break;

            default:
                $rule = "@^{$rule}(/.*)?$@";
        }

        return !!preg_match($rule, $uri);
    }
}
