<?php
/**
 * @author   Fung Wing Kit <wengee@gmail.com>
 * @version  2019-03-02 10:44:53 +0800
 */
namespace SlimExtra\Http;

use Exception;
use Slim\Http\Response as SlimResponse;

class Response extends SlimResponse
{
    protected $cookies = [];

    public function json(...$args)
    {
        $data = ['errmsg' => null, 'errcode' => -1];
        foreach ($args as $arg) {
            if ($arg instanceof Exception) {
                $data['errcode'] = $arg->getCode() ?: -1;
                $data['errmsg'] = $arg->getMessage();
            } elseif (is_int($arg)) {
                $data['errcode'] = $arg;
            } elseif (is_string($arg)) {
                $data['errmsg'] = $arg;
            } else {
                $data = array_merge($data, (array) $arg);
            }
        }

        return $this->withJson($data, 200);
    }

    public function setCookie(string $name, ?string $value = null, int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = true)
    {
        if ($value === null) {
            $expire = 1;
        }

        $clone = clone $this;
        $domain = $domain ?: config('cookie.domain');
        $clone->cookies[$name] = compact('value', 'expire', 'path', 'domain', 'secure', 'httponly');
        return $clone;
    }

    public function getHeaders()
    {
        $headers = (array) parent::getHeaders();
        if ($this->cookies) {
            $headers['Set-Cookie'] = [];
            foreach ($this->cookies as $name => $properties) {
                $headers['Set-Cookie'][] = $this->parseCookie($name, $properties);
            }
        }

        return $headers;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    protected function parseCookie($name, array $properties): string
    {
        $result = urlencode($name) . '=' . urlencode($properties['value']);

        if (isset($properties['domain'])) {
            $result .= '; domain=' . $properties['domain'];
        }

        if (isset($properties['path'])) {
            $result .= '; path=' . $properties['path'];
        }

        if (isset($properties['expire'])) {
            $timestamp = (int) $properties['expire'];

            if ($timestamp !== 0) {
                $result .= '; expires=' . gmdate('D, d-M-Y H:i:s e', $timestamp);
            }
        }

        if (isset($properties['secure']) && $properties['secure']) {
            $result .= '; secure';
        }

        if (isset($properties['hostonly']) && $properties['hostonly']) {
            $result .= '; HostOnly';
        }

        if (isset($properties['httponly']) && $properties['httponly']) {
            $result .= '; HttpOnly';
        }

        if (isset($properties['samesite']) && in_array(strtolower($properties['samesite']), ['lax', 'strict'], true)) {
            // While strtolower is needed for correct comparison, the RFC doesn't care about case
            $result .= '; SameSite=' . $properties['samesite'];
        }

        return $result;
    }
}
